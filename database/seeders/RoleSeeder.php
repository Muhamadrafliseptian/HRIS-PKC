<?php

namespace Database\Seeders;

use App\Models\Roles;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Employee'
            ],
            [
                'name' => 'Superadmin'
            ],
        ];

        foreach ($roles as $role) {
            $cek = Roles::where('name', $role['name'])->first();
            if ($cek == null) {
                $save = new Roles();
                $save->name = $role['name'];
                $save->saveOrFail();
            }
        }
    }
}
