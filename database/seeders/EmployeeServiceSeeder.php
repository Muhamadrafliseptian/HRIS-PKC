<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Reguler',
                'description' => 'gak pernah ada shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'IGD',
                'description' => 'shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Farmasi',
                'description' => 'shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Lab',
                'description' => 'shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Ruang Bersalin',
                'description' => 'shift',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('employee_services')->insert($categories);
    }
}
