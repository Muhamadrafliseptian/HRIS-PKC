<?php

namespace App\Http\Controllers\Devices;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\Branch;
use App\Models\EmployeeStatus;
use Carbon\Carbon;
use Exception;
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

            if ($this->isReachable($validated['ip_address'], $validated['port'], 1)) {

                try {
                    $zk = new ZKTeco($validated['ip_address'], $validated['port']);

                    if ($zk->connect()) {
                        $status = 1;
                        $time = $zk->getTime();
                        $zk->disconnect();
                    }

                } catch (Exception $e) {
                    $status = 0;
                }
            }

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

    public function readDevices()
    {
        $devices = BiometricDevice::with(['dtbranch', 'category'])->get();

        $results = $devices->map(function ($device) {

            $base = [
                'id' => $device->id,
                'name' => $device->name,
                'ip' => $device->ip_address,
                'port' => $device->port,
                'branch' => $device->dtbranch->name ?? null,
                'category' => $device->category->name ?? null,
            ];

            if (!$device->ip_address || !$device->port) {
                return array_merge($base, [
                    'status' => 'no_config',
                    'synced' => false,
                ]);
            }

            if (!$this->isReachable($device->ip_address, $device->port, 1)) {
                return array_merge($base, [
                    'status' => 'offline',
                    'synced' => false,
                ]);
            }

            try {
                $zk = new ZKTeco($device->ip_address, $device->port);

                socket_set_option($zk->_zkclient, SOL_SOCKET, SO_RCVTIMEO, [
                    'sec' => 2,
                    'usec' => 0,
                ]);

                if ($zk->connect()) {

                    $deviceTime = $zk->getTime();
                    $zk->disconnect();

                    return array_merge($base, [
                        'status' => 'online',
                        'synced' => true,
                        'device_time' => $deviceTime,
                    ]);
                }

                return array_merge($base, [
                    'status' => 'offline',
                    'synced' => false,
                ]);

            } catch (\Exception $e) {
                return errorHandler($e);
            }
        })->values();

        return successHandler([
            'status' => true,
            'data' => $results,
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

        if (!$this->isReachable($device->ip_address, $device->port, 1)) {
            return successHandler(array_merge($response, [
                'status' => 'offline',
            ]));
        }

        try {
            $zk = new ZKTeco($device->ip_address, $device->port);

            socket_set_option($zk->_zkclient, SOL_SOCKET, SO_RCVTIMEO, [
                'sec' => 2,
                'usec' => 0,
            ]);

            if (!$zk->connect()) {
                return successHandler(array_merge($response, [
                    'status' => 'offline',
                ]));
            }

            $zk->disconnect();

            return successHandler(array_merge($response, [
                'status' => 'online',
            ]));

        } catch (\Exception $e) {
            return errorHandler($e);
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
