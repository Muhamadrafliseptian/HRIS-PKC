<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            ShiftCategorySeeder::class,
            ShiftSeeder::class,
            BiometricCategorySeeder::class,
            BiometricDeviceSeeder::class,
            EmployeeServiceSeeder::class,
            EmployeeStatusSeeder::class,
            BranchConfigSeeder::class,
            BranchSeeder::class,
            RoleSeeder::class,
            MenuSeeder::class,
            UserSeeder::class,
        ]);
    }
}
