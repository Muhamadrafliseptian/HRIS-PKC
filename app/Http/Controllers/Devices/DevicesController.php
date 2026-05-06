<?php

namespace App\Http\Controllers\Devices;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\EmployeeStatus;
use Carbon\Carbon;
use Exception;
use Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Rats\Zkteco\Lib\ZKTeco;
use Illuminate\Validation\ValidationException;

class DevicesController extends Controller
{
    public function index()
    {
        $branchs = Branch::where('status', 1)->get(['id', 'name'])->map(function ($branchs) {
            return [
                'value' => $branchs->id,
                'label' => $branchs->name
            ];
        });

        $category = EmployeeStatus::orderBy('id', 'desc')->get(['id', 'name'])->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name
            ];
        });

        $response = [
            'branchs' => $branchs,
            'categories' => $category
        ];

        return Inertia::render('Devices/Index', $response);
    }

    public function create(Request $request)
    {
        try {
            $validated = $request->validate([
                'branch' => 'required|exists:branchs,id',
                'biometric_category_id' => 'required|exists:employee_status,id',
                'name' => 'required|string|max:255',
                'ip_address' => 'required|ip',
                'port' => 'required|numeric',
            ]);

            $status = 0;
            $time = null;

            $device = BiometricDevice::create([
                'branch' => $validated['branch'],
                'biometric_category_id' => $validated['biometric_category_id'],
                'name' => $validated['name'],
                'ip_address' => $validated['ip_address'],
                'port' => $validated['port'],
                'status' => $status,
                'last_sync' => $time ? now() : null,
            ]);

            return successHandler();

        } catch (ValidationException $ev) {
            return errorValidationHandler($ev);
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function update(Request $request)
    {
        try {
            $validated = $request->validate([
                'branch' => 'required|exists:branchs,id',
                'biometric_category_id' => 'required|exists:employee_status,id',
                'name' => 'required|string|max:255',
                'ip_address' => 'required|ip',
                'port' => 'required|numeric',
            ]);

            $device = BiometricDevice::findOrFail($request->id);

            $status = 0;
            $time = null;

            $device->update([
                'branch' => $validated['branch'],
                'biometric_category_id' => $validated['biometric_category_id'],
                'name' => $validated['name'],
                'ip_address' => $validated['ip_address'],
                'port' => $validated['port'],
                'status' => $status,
                'last_sync' => $time ? now() : null,
            ]);

            return successHandler();

        } catch (ValidationException $ev) {
            return errorValidationHandler($ev);
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function readDevices()
    {
        $devices = BiometricDevice::with(['dtbranch', 'category'])->get();

        $payload = $devices
            ->filter(fn($d) => $d->ip_address && $d->port)
            ->map(fn($d) => [
                'ip' => $d->ip_address,
                'port' => $d->port,
            ])
            ->values();

        $statusMap = [];

        try {
            if ($payload->isNotEmpty()) {

                $response = Http::timeout(10)
                    ->withHeaders([
                        'x-api-key' => env('ZK_API_KEY')
                    ])
                    ->post('http://127.0.0.1:8001/check-multiple', [
                        'devices' => $payload
                    ]);

                if ($response->successful()) {
                    $json = $response->json();

                    foreach ($json['data'] ?? [] as $item) {
                        $statusMap[$item['ip']] = $item['status'];
                    }
                }
            }

        } catch (\Exception $e) {
        }

        $results = $devices->map(function ($device) use ($statusMap) {

            $base = [
                'id' => $device->id,
                'name' => $device->name,
                'ip' => $device->ip_address,
                'port' => $device->port,
                'branch_id' => $device->branch,
                'branch_name' => $device->dtbranch->name ?? null,
                'category_id' => $device->biometric_category_id,
                'category_name' => $device->category->name ?? null,
            ];

            if (!$device->ip_address || !$device->port) {
                return array_merge($base, [
                    'status' => 'no_config',
                    'synced' => false,
                ]);
            }

            $status = $statusMap[$device->ip_address] ?? 'offline';

            return array_merge($base, [
                'status' => $status,
                'synced' => $status === 'online',
            ]);
        });

        return successHandler([
            'status' => true,
            'data' => $results->values(),
        ]);
    }

    public function checkDevices($id)
    {
        $device = BiometricDevice::with(['dtbranch', 'category'])
            ->findOrFail($id);

        $response = [
            'id' => $device->id,
            'device_name' => $device->name,
            'ip' => $device->ip_address,
            'port' => $device->port,
            'branch' => $device->dtbranch?->name,
            'category' => $device->category?->name,
        ];

        if (!$device->ip_address || !$device->port) {
            return successHandler(array_merge($response, [
                'status' => 'no_config',
            ]));
        }

        try {
            $res = Http::timeout(10)
                ->withHeaders([
                    'x-api-key' => env('ZK_API_KEY')
                ])
                ->get('http://127.0.0.1:8001/device-time', [
                    'ip' => $device->ip_address,
                    'port' => $device->port,
                ]);

            if (!$res->successful()) {
                return successHandler(array_merge($response, [
                    'status' => 'offline',
                ]));
            }

            $json = $res->json();

            if (!$json['success']) {
                return successHandler(array_merge($response, [
                    'status' => 'offline',
                ]));
            }

            return successHandler(array_merge($response, [
                'status' => 'online',
                'device_time' => $json['after'],
                'server_time' => $json['server_time'],
                'difference' => $json['difference_after'],
                'device_info' => $json['data'],
                'synced' => $json['synced'],
            ]));

        } catch (\Exception $e) {
            return successHandler(array_merge($response, [
                'status' => 'offline',
                'error' => $e->getMessage(),
            ]));
        }
    }

    private function isReachable($ip, $port, $timeout = 1)
    {
        $conn = @fsockopen($ip, $port, $errno, $errstr, $timeout);
        if ($conn) {
            fclose($conn);
            return true;
        }
        return false;
    }
}
