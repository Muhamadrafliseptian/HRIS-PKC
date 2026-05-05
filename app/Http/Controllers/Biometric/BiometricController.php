<?php

namespace App\Http\Controllers\Biometric;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\BiometricUsers;
use App\Models\Branch;
use App\Models\EmployeeStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Exception;

class BiometricController extends Controller
{
    public function index()
    {
        try {
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

            $device = BiometricDevice::orderBy('id', 'desc')->with('dtbranch')->get(['id', 'name', 'branch'])->map(function ($device) {
                return [
                    'value' => $device->id,
                    'label' => $device->name . " - " . $device->dtbranch?->name
                ];
            });

            $status = [
                [
                    'value' => 1,
                    'label' => 'active',
                ],
                [
                    'value' => 2,
                    'label' => 'non active',
                ],
            ];

            $response = [
                'branchs' => $branchs,
                'categories' => $category,
                'status' => $status,
                'devices' => $device
            ];

            return Inertia::render('Biometric/Index', $response);

        } catch (\Exception $e) {
            return errorHandler($e);
        }
    }
    public function read()
    {
        try {
            $data = BiometricUsers::with('device')->orderBy("created_at", "desc")->get();

            return successHandler($data);
        } catch (Exception $err) {
            return errorHandler($err);
        }
    }
    public function sync(Request $request)
    {
        try {
            set_time_limit(0);

            $device = BiometricDevice::findOrFail($request->device);

            $response = Http::timeout(120)
            ->withHeaders([
                'x-api-key' => env('ZK_API_KEY')
            ])
                ->post('http://127.0.0.1:8001/users', [
                    'ip' => $device->ip_address,
                    'port' => (int) $device->port,
                ]);

            if (!$response->successful()) {
                throw new Exception('Python API error: ' . $response->body());
            }

            $json = $response->json();

            if (!$json || !isset($json['success']) || $json['success'] !== true) {
                throw new Exception('Invalid Python response');
            }

            $users = $json['data'] ?? [];

            if (empty($users)) {
                return successHandler([
                    'message' => 'Tidak ada user dari device'
                ]);
            }

            $now = now();

            $data = collect($users)
                ->filter(fn($u) => !empty($u['user_id']))
                ->map(function ($u) use ($device, $now) {
                    return [
                        'device_id' => $device->id,
                        'user_id' => $u['user_id'],
                        'uid' => $u['uid'] ?? null,
                        'name' => $u['name'] ?? null,
                        'role' => $u['role'] ?? 0,
                        'synced_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                });

            if ($data->isEmpty()) {
                return successHandler([
                    'message' => 'Tidak ada data valid'
                ]);
            }

            foreach ($data->chunk(500) as $chunk) {
                BiometricUsers::upsert(
                    $chunk->toArray(),
                    ['device_id', 'uid'],
                    ['user_id', 'name', 'role', 'synced_at', 'updated_at']
                );
            }

            return successHandler([
                'message' => 'Sync user berhasil',
                'total' => $data->count()
            ]);

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function create(Request $request)
    {
        try {
            $request->validate([
                'device_id' => 'required|exists:biometric_devices,id',
                'user_id' => 'required',
                'name' => 'required'
            ]);

            $device = BiometricDevice::findOrFail($request->device_id);

            $existing = BiometricUsers::withTrashed()
                ->where('device_id', $device->id)
                ->where('user_id', $request->user_id)
                ->first();

            $uid = (BiometricUsers::where('device_id', $device->id)->max('uid') ?? 0) + 1;

            $response = Http::timeout(120)
                ->retry(3, 1000)
                ->withHeaders([
                    'x-api-key' => env('ZK_API_KEY')
                ])
                ->post('http://127.0.0.1:8001/set-user', [
                    'ip' => $device->ip_address,
                    'port' => (int) $device->port,
                    'uid' => $uid,
                    'name' => $request->name,
                    'user_id' => $request->user_id
                ]);

            if (!$response->successful()) {
                throw new Exception(
                    $response->json()['detail'] ?? $response->body()
                );
            }

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                throw new Exception($result['message'] ?? 'Gagal create user di device');
            }

            // 🔥 HANDLE DB
            if ($existing) {

                // kalau soft delete → restore
                if ($existing->trashed()) {
                    $existing->restore();
                }

                // update data
                $existing->update([
                    'uid' => $uid,
                    'name' => $request->name,
                    'role' => 0,
                    'synced_at' => now(),
                ]);

            } else {

                // insert baru
                BiometricUsers::create([
                    'uid' => $uid,
                    'user_id' => $request->user_id,
                    'name' => $request->name,
                    'device_id' => $device->id,
                    'role' => 0,
                    'synced_at' => now(),
                ]);
            }

            return successHandler([
                'message' => 'User berhasil ditambahkan / diupdate'
            ]);

        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $request->validate([
                'device' => 'required|exists:biometric_devices,id',
                'user_id' => 'required'
            ]);

            $device = BiometricDevice::findOrFail($request->device);

            $user = BiometricUsers::where('user_id', $request->user_id)
                ->where('device_id', $device->id)
                ->firstOrFail();

            $response = Http::timeout(120)
                ->retry(3, 1000)
                ->withHeaders([
                    'x-api-key' => env('ZK_API_KEY')
                ])
                ->post('http://127.0.0.1:8001/delete-user', [
                    'ip' => $device->ip_address,
                    'port' => (int) $device->port,
                    'uid' => $user->uid,
                ]);

            if (!$response->successful()) {
                throw new \Exception(
                    $response->json()['detail'] ?? $response->body()
                );
            }

            $result = $response->json();

            if (!isset($result['success']) || !$result['success']) {
                throw new \Exception(
                    $result['message'] ?? 'Gagal hapus user di device'
                );
            }

            $user->delete();

            return successHandler([
                'message' => 'User berhasil dihapus'
            ]);

        } catch (ValidationException $e) {
            return errorHandler($e->errors());
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return errorHandler('User atau device tidak ditemukan');
        } catch (\Exception $e) {
            return errorHandler($e);
        }
    }
    public function transferUser(Request $request)
    {
        try {
            $request->validate([
                'from_device' => 'required|exists:biometric_devices,id',
                'to_device' => 'required|exists:biometric_devices,id',
                'user_id' => 'required'
            ]);

            if ($request->from_device == $request->to_device) {
                throw new Exception('Device asal dan tujuan tidak boleh sama');
            }

            $fromDevice = BiometricDevice::findOrFail($request->from_device);
            $toDevice = BiometricDevice::findOrFail($request->to_device);

            $user = BiometricUsers::where('user_id', $request->user_id)
                ->where('device_id', $fromDevice->id)
                ->firstOrFail();

            $newUid = (BiometricUsers::where('device_id', $toDevice->id)->max('uid') ?? 0) + 1;

            $existing = BiometricUsers::withTrashed()
                ->where('device_id', $toDevice->id)
                ->where('user_id', $user->user_id)
                ->first();

            $createResponse = Http::timeout(120)
                ->retry(3, 1000)
                ->withHeaders([
                    'x-api-key' => env('ZK_API_KEY')
                ])
                ->post('http://127.0.0.1:8001/set-user', [
                    'ip' => $toDevice->ip_address,
                    'port' => (int) $toDevice->port,
                    'uid' => $newUid,
                    'name' => $user->name,
                    'user_id' => $user->user_id
                ]);

            if (!$createResponse->successful()) {
                throw new Exception(
                    $createResponse->json()['detail'] ?? $createResponse->body()
                );
            }

            $createResult = $createResponse->json();

            if (!($createResult['success'] ?? false)) {
                throw new Exception($createResult['message'] ?? 'Gagal create user di device tujuan');
            }

            if ($existing) {

                if ($existing->trashed()) {
                    $existing->restore();
                }

                $existing->update([
                    'uid' => $newUid,
                    'name' => $user->name,
                    'synced_at' => now()
                ]);

            } else {

                BiometricUsers::create([
                    'uid' => $newUid,
                    'user_id' => $user->user_id,
                    'name' => $user->name,
                    'device_id' => $toDevice->id,
                    'role' => 0,
                    'synced_at' => now(),
                ]);
            }

            $deleteResponse = Http::timeout(120)
                ->retry(3, 1000)
                ->withHeaders([
                    'x-api-key' => env('ZK_API_KEY')
                ])
                ->post('http://127.0.0.1:8001/delete-user', [
                    'ip' => $fromDevice->ip_address,
                    'port' => (int) $fromDevice->port,
                    'uid' => $user->uid,
                ]);

            if (!$deleteResponse->successful()) {
                throw new Exception(
                    $deleteResponse->json()['detail'] ?? $deleteResponse->body()
                );
            }

            $deleteResult = $deleteResponse->json();

            if (!($deleteResult['success'] ?? false)) {
                throw new Exception($deleteResult['message'] ?? 'Gagal delete user dari device asal');
            }

            $user->delete();

            return successHandler([
                'message' => 'User berhasil dipindahkan'
            ]);

        } catch (ValidationException $e) {
            return errorHandler($e);
        } catch (Exception $e) {
            return errorHandler($e->getMessage());
        }
    }
}