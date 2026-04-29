<?php

namespace App\Http\Controllers\Biometric;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Models\BiometricUsers;
use App\Models\Branch;
use App\Models\EmployeeStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Rats\Zkteco\Lib\ZKTeco;
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
                ->post('http://192.168.118.31:8000/users', [
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
                    ['device_id', 'user_id'],
                    ['uid', 'name', 'role', 'synced_at', 'updated_at']
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
            $zk = new ZKTeco($this->ip, $this->port);

            if (!$zk->connect()) {
                return errorHandler('Gagal connect ke device');
            }

            $lastUid = BiometricUsers::max('uid') ?? 0;
            $uid = $lastUid + 1;

            $zk->setUser(
                $uid,
                $request->user_id,
                $request->name,
                '',
                0
            );

            BiometricUsers::create([
                'uid' => $uid,
                'user_id' => $request->user_id,
                'name' => $request->name,
                'device_id' => $request->device_id,
                'role' => 0,
                'synced_at' => now(),
            ]);

            $zk->disconnect();

            return successHandler();
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function transferUser(Request $request)
    {
        try {
            $idOrigin = $request->id_origin;
            $idDestination = $request->id_destination;
            $port = 4370;
            $userId = $request->user_id;

            $deviceA = BiometricDevice::where('id', $idOrigin)->first();
            $deviceB = BiometricDevice::where('id', $idDestination)->first();

            if (!$deviceA || !$deviceB) {
                throw new Exception("Device tidak ditemukan");
            }

            $zkA = new ZKTeco($deviceA->ip_address, $port);
            if (!$zkA->connect()) {
                throw new Exception("Gagal connect ke device {$deviceA->name}");
            }

            $zkA->disableDevice();
            $usersA = $zkA->getUser();
            $zkA->enableDevice();

            if (!$usersA) {
                $zkA->disconnect();
                throw new Exception("Tidak ada user di device {$deviceA->name}");
            }

            $userA = collect($usersA)->firstWhere('userid', $userId);
            if (!$userA) {
                $zkA->disconnect();
                throw new Exception("User tidak ditemukan di device {$deviceA->name}");
            }

            $zkB = new ZKTeco($deviceB->ip_address, $port);
            if (!$zkB->connect()) {
                $zkA->disconnect();
                throw new Exception("Gagal connect ke device {$deviceB->name}");
            }

            $zkB->disableDevice();
            $usersB = $zkB->getUser() ?? [];
            $uidExists = collect($usersB)->contains(fn($u) => $u['uid'] == $userA['uid']);

            if ($uidExists) {
                $zkB->enableDevice();
                $zkB->disconnect();
                $zkA->disconnect();
                throw new Exception("UID {$userA['uid']} sudah digunakan di device {$deviceB->name}", 422);
            }

            $zkB->setUser(
                $userA['uid'],
                $userA['userid'],
                $userA['name'],
                '',
                $userA['role']
            );
            $zkB->enableDevice();
            $zkB->disconnect();

            // ── Hapus user dari device asal (setelah berhasil set di tujuan) ──
            $zkA->disableDevice();
            $zkA->removeUser($userA['uid']); // ← method yang benar untuk rats/zkteco
            $zkA->enableDevice();
            $zkA->disconnect();

            // ── Update database ──
            BiometricUsers::updateOrCreate(
                ['user_id' => $userA['userid'], 'device_id' => $deviceB->id],
                [
                    'uid' => $userA['uid'],
                    'name' => $userA['name'],
                    'role' => $userA['role'],
                    'synced_at' => now(),
                ]
            );

            BiometricUsers::where('user_id', $userA['userid'])
                ->where('device_id', $deviceA->id)
                ->delete();

            return successHandler();

        } catch (\Exception $e) {
            return errorHandler($e);
        }
    }

    public function destroy(Request $request)
    {
        try {
            $device = BiometricDevice::findOrFail($request->device);

            $user = BiometricUsers::where('user_id', $request->user_id)
                ->where('device_id', $device->id)
                ->firstOrFail();

            $zk = new ZKTeco($device->ip_address, $device->port);

            if (!$zk->connect()) {
                throw new Exception('Gagal connect ke device', 422);
            }

            $zk->removeUser($user->uid);

            $user->delete();

            $zk->disconnect();

            return successHandler();

        } catch (\Exception $e) {
            return errorHandler($e);
        }
    }
}