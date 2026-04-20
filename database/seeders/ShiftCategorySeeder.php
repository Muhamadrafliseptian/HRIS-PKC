<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftCategorySeeder extends Seeder
{
    public function run()
    {
        DB::table('shift_categories')->truncate();

        $categories = [
            [
                'name' => 'REGULER',
                'description' => 'Shift umum non unit khusus',
            ],
            [
                'name' => 'UGD',
                'description' => 'Unit Gawat Darurat',
            ],
            [
                'name' => 'FARMASI',
                'description' => 'Bagian Farmasi',
            ],
            [
                'name' => 'LABORATORIUM',
                'description' => 'Unit Laboratorium',
            ],
            [
                'name' => 'RUANG BERSALIN',
                'description' => 'Ruang Bersalin / Kebidanan',
            ],
            [
                'name' => 'OFF',
                'description' => 'Libur / Hari Tidak Masuk',
            ],
        ];

        foreach ($categories as $cat) {
            DB::table('shift_categories')->insert([
                'name' => $cat['name'],
                'description' => $cat['description'],
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}