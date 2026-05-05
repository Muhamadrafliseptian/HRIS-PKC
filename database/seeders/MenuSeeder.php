<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            [
                'key' => 'dashboard',
                'parent' => null,
                'ordering' => 1,
                'is_active' => 1,
                'icon' => 'dashboard',
                'label' => 'Dashboard',
                'url' => '/',
                'childrens' => []
            ],
            [
                'key' => 'attendance',
                'parent' => null,
                'ordering' => 2,
                'is_active' => 1,
                'icon' => 'attendance',
                'label' => 'Attendance',
                'url' => '/attendance',
                'childrens' => []
            ],
            [
                'key' => 'employee',
                'parent' => null,
                'ordering' => 3,
                'is_active' => 1,
                'icon' => 'employee',
                'label' => 'Employee',
                'url' => '/employee',
                'childrens' => []
            ],
            [
                'key' => 'biometric',
                'parent' => null,
                'ordering' => 4,
                'is_active' => 1,
                'icon' => 'biometric',
                'label' => 'Biometric',
                'url' => null,
                'childrens' => [
                    [
                        'key' => 'biometric-users',
                        'ordering' => 1,
                        'is_active' => 1,
                        'icon' => 'users',
                        'label' => 'Users',
                        'url' => '/biometric/users',
                        'childrens' => []
                    ],
                    [
                        'key' => 'biometric-devices',
                        'ordering' => 2,
                        'is_active' => 1,
                        'icon' => 'devices',
                        'label' => 'Devices',
                        'url' => '/biometric/devices',
                        'childrens' => []
                    ],
                ]
            ],
            [
                'key' => 'master',
                'parent' => null,
                'ordering' => 5,
                'is_active' => 1,
                'icon' => 'master',
                'label' => 'Master',
                'url' => null,
                'childrens' => [
                    [
                        'key' => 'branch',
                        'ordering' => 1,
                        'is_active' => 1,
                        'icon' => 'branch',
                        'label' => 'Branch',
                        'url' => '/master/branch',
                        'childrens' => []
                    ],
                ]
            ],
            [
                'key' => 'manage-shift',
                'parent' => null,
                'ordering' => 6,
                'is_active' => 1,
                'icon' => 'shift',
                'label' => 'Manage Shift',
                'url' => null,
                'childrens' => [
                    [
                        'key' => 'manage-shift-master',
                        'ordering' => 1,
                        'is_active' => 1,
                        'icon' => 'shift_master',
                        'label' => 'Master',
                        'url' => '/manage/shift/master',
                        'childrens' => []
                    ],
                    [
                        'key' => 'manage-shift-assign',
                        'ordering' => 2,
                        'is_active' => 1,
                        'icon' => 'shift_assign',
                        'label' => 'Assign',
                        'url' => '/manage/shift/assignment',
                        'childrens' => []
                    ],
                ]
            ],
            [
                'key' => 'report',
                'parent' => null,
                'ordering' => 7,
                'is_active' => 1,
                'icon' => 'report',
                'label' => 'Report',
                'url' => null,
                'childrens' => [
                    [
                        'key' => 'report-attendance',
                        'ordering' => 1,
                        'is_active' => 1,
                        'icon' => 'report_attendance',
                        'label' => 'Attendance',
                        'url' => '/report/attendance/log',
                        'childrens' => []
                    ],
                ]
            ],
            [
                'key' => 'setting',
                'parent' => null,
                'ordering' => 8,
                'is_active' => 1,
                'icon' => 'setting',
                'label' => 'Setting',
                'url' => null,
                'childrens' => [
                    [
                        'key' => 'setting-users',
                        'ordering' => 1,
                        'is_active' => 1,
                        'icon' => 'setting_users',
                        'label' => 'Users',
                        'url' => '/setting/users',
                        'childrens' => []
                    ],
                ]
            ],
        ];

        foreach ($menus as $menu) {
            $seed = Menu::where('key', $menu['key'])->whereNull('parent')->first();

            if (!$seed) {
                $seed = Menu::create([
                    'parent' => null,
                    'label' => $menu['label'],
                    'key' => $menu['key'],
                    'icon' => $menu['icon'],
                    'url' => $menu['url'],
                    'ordering' => $menu['ordering'],
                    'is_active' => $menu['is_active'],
                ]);
            }

            if (!empty($menu['childrens'])) {
                foreach ($menu['childrens'] as $child) {
                    $cek = Menu::where('url', $child['url'])->first();

                    if (!$cek) {
                        Menu::create([
                            'parent' => $seed->id,
                            'label' => $child['label'],
                            'key' => $child['key'],
                            'icon' => $child['icon'],
                            'url' => $child['url'],
                            'ordering' => $child['ordering'],
                            'is_active' => $child['is_active'],
                        ]);
                    }
                }
            }
        }
    }
}