<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            // Puskesmas Utama — Kebon Jeruk
            [
                'branch' => 1,
                'lat' => '-6.19257205',
                'lng' => '106.76972549',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Kelapa Dua
            [
                'branch' => 2,
                'lat' => '-6.2095281',
                'lng' => '106.7688125',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Kebon Jeruk (lokasi dalam Kelurahan Kebon Jeruk)
            [
                'branch' => 3,
                'lat' => '-6.19257205',
                'lng' => '106.76972549',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Duri Kepa
            [
                'branch' => 4,
                'lat' => '-6.1800',
                'lng' => '106.7500',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Sukabumi Selatan
            [
                'branch' => 5,
                'lat' => '-6.2000',
                'lng' => '106.7550',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Sukabumi Utara
            [
                'branch' => 6,
                'lat' => '-6.1850',
                'lng' => '106.7600',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Kedoya Selatan
            [
                'branch' => 7,
                'lat' => '-6.1700',
                'lng' => '106.7400',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Pustu Kedoya Utara
            [
                'branch' => 8,
                'lat' => '-6.1600',
                'lng' => '106.7350',
                'time_zone' => 'Asia/Jakarta',
                'time_zone_label' => 'WIB',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('branch_configs')->insert($configs);
    }
}