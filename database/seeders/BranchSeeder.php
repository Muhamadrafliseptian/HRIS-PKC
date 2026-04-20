<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        $branches = [
            [
                'name' => 'Puskesmas Kecamatan Kebon Jeruk',
                'province' => '31',      // DKI Jakarta
                'city' => '3174',        // Jakarta Barat
                'district' => '3174060', // Kebon Jeruk
                'village' => '3174060004', // Kebon Jeruk
                'detail' => 'Puskesmas Kecamatan Kebon Jeruk',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Kelapa Dua',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060001',
                'detail' => 'Puskesmas Pembantu Kelapa Dua',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Kebon Jeruk',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060004',
                'detail' => 'Puskesmas Pembantu Kebon Jeruk',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Duri Kepa',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060002',
                'detail' => 'Puskesmas Pembantu Duri Kepa',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Sukabumi Selatan',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060007',
                'detail' => 'Puskesmas Pembantu Sukabumi Selatan',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Sukabumi Utara',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060008',
                'detail' => 'Puskesmas Pembantu Sukabumi Utara',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Kedoya Selatan',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060005',
                'detail' => 'Puskesmas Pembantu Kedoya Selatan',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pustu Kedoya Utara',
                'province' => '31',
                'city' => '3174',
                'district' => '3174060',
                'village' => '3174060006',
                'detail' => 'Puskesmas Pembantu Kedoya Utara',
                'status' => true,
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('branchs')->insert($branches);
    }
}