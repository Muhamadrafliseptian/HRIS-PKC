<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Menu;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $menuIds = Menu::pluck('id')->toArray();
        $permissions = implode(',', $menuIds);

        if (!User::where('email', 'admin@local.test')->exists()) {
            User::create([
                'name' => 'Super Admin',
                'email' => 'admin@local.test',
                'password' => Hash::make('admin123'),
                'permission' => '',
                'is_active' => 1,
                'is_super' => 1,
                'is_sync' => 1,
                'role' => 0,
            ]);
        }

        $employees = Employee::all();

        foreach ($employees as $employee) {

            if (User::where('id', $employee->user_id)->exists()) {
                continue;
            }

            $email = $employee->email 
                ?? 'user' . $employee->user_id . '@local.test';

            User::create([
                'id' => $employee->user_id,
                'name' => $employee->name ?? 'User ' . $employee->user_id,
                'email' => $email,
                'password' => Hash::make($employee->user_id),
                'permission' => $permissions, 
                'is_active' => 1,
                'is_super' => 0,
                'is_sync' => 1,
                'role' => 1,
            ]);
        }
    }
}