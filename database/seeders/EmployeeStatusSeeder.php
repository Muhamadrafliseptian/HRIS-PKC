<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'ASN',
                'description' => 'Pegawai Negeri Sipil',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'NON ASN',
                'description' => 'Pegawai Non Negeri Sipil / Kontrak',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'PJLP',
                'description' => 'Penyedia Jasa Lainnya Perorangan',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('employee_status')->insert($categories);
    }
}
