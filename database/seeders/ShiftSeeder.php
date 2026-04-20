<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShiftSeeder extends Seeder
{
    public function run()
    {
        DB::table('shifts')->truncate();
        DB::table('shift_details')->truncate();

        // 🔥 ambil kategori
        $categories = DB::table('shift_categories')->pluck('id', 'name');

        $shifts = [
            // ================= REGULER =================
            ['code' => 'S01', 'name' => '07:30 - 16:00', 'type' => 'single', 'category' => 'REGULER'],
            ['code' => 'S02', 'name' => '07:30 - 16:30', 'type' => 'single', 'category' => 'REGULER'],
            ['code' => 'OFF', 'name' => 'Libur', 'type' => 'single', 'is_off' => 1, 'category' => 'OFF'],

            ['code' => 'S03', 'name' => '07:30 - 14:00 (UGD/Farmasi)', 'type' => 'single', 'category' => 'UGD'],
            ['code' => 'S04', 'name' => '14:00 - 21:00 (UGD/Farmasi)', 'type' => 'single', 'category' => 'UGD'],
            ['code' => 'S05', 'name' => '21:00 - 07:30 (UGD/Farmasi)', 'type' => 'single', 'category' => 'UGD'],

            ['code' => 'S06', 'name' => '07:30 - 16:00 (Lab Pagi)', 'type' => 'single', 'category' => 'LABORATORIUM'],
            ['code' => 'S07', 'name' => '13:00 - 21:00 (Lab Siang)', 'type' => 'single', 'category' => 'LABORATORIUM'],

            ['code' => 'S08', 'name' => '07:30 - 14:00 (RB Pagi)', 'type' => 'single', 'category' => 'RUANG BERSALIN'],
            ['code' => 'S09', 'name' => '14:00 - 20:30 (RB Siang)', 'type' => 'single', 'category' => 'RUANG BERSALIN'],
            ['code' => 'S10', 'name' => '20:30 - 07:30 (RB Malam)', 'type' => 'single', 'category' => 'RUANG BERSALIN'],

            // ================= DOUBLE =================
            ['code' => 'S11', 'name' => '07:30 - 21:00 (UGD PS)', 'type' => 'double', 'category' => 'UGD'],
            ['code' => 'S12', 'name' => '14:00 - 07:30 (UGD SM)', 'type' => 'double', 'category' => 'UGD'],

            ['code' => 'S15', 'name' => '07:30 - 20:30 (RB PS)', 'type' => 'double', 'category' => 'RUANG BERSALIN'],
            ['code' => 'S16', 'name' => '14:00 - 07:30 (RB SM)', 'type' => 'double', 'category' => 'RUANG BERSALIN'],

            ['code' => 'S19', 'name' => '07:30 - 21:00 (Lab PS)', 'type' => 'double', 'category' => 'LABORATORIUM'],

            // ================= SPLIT =================
            ['code' => 'S13', 'name' => 'Pagi + Malam (UGD)', 'type' => 'split', 'category' => 'UGD'],
            ['code' => 'S14', 'name' => 'Malam + Pagi (UGD)', 'type' => 'split', 'category' => 'UGD'],

            ['code' => 'S17', 'name' => 'Pagi + Malam (RB)', 'type' => 'split', 'category' => 'RUANG BERSALIN'],
            ['code' => 'S18', 'name' => 'Malam + Pagi (RB)', 'type' => 'split', 'category' => 'RUANG BERSALIN'],
        ];

        $shiftIds = [];

        foreach ($shifts as $shift) {
            $id = DB::table('shifts')->insertGetId([
                'code' => $shift['code'],
                'name' => $shift['name'],
                'type' => $shift['type'],
                'is_off' => $shift['is_off'] ?? 0,
                'shift_category' => $categories[$shift['category']] ?? null,
        
                'tolerance_before_in' => 10,
                'tolerance_after_in' => 10,
                'tolerance_before_out' => 10,
                'tolerance_after_out' => 10,
        
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $shiftIds[$shift['code']] = $id;
        }

        $details = [
            // ===== SINGLE =====
            ['code' => 'S01', 'in' => '07:30', 'out' => '16:00', 'cross' => 0],
            ['code' => 'S02', 'in' => '07:30', 'out' => '16:30', 'cross' => 0],
            ['code' => 'S03', 'in' => '07:30', 'out' => '14:00', 'cross' => 0],
            ['code' => 'S04', 'in' => '14:00', 'out' => '21:00', 'cross' => 0],
            ['code' => 'S05', 'in' => '21:00', 'out' => '07:30', 'cross' => 1],
            ['code' => 'S06', 'in' => '07:30', 'out' => '16:00', 'cross' => 0],
            ['code' => 'S07', 'in' => '13:00', 'out' => '21:00', 'cross' => 0],
            ['code' => 'S08', 'in' => '07:30', 'out' => '14:00', 'cross' => 0],
            ['code' => 'S09', 'in' => '14:00', 'out' => '20:30', 'cross' => 0],
            ['code' => 'S10', 'in' => '20:30', 'out' => '07:30', 'cross' => 1],

            // ===== DOUBLE =====
            ['code' => 'S11', 'in' => '07:30', 'out' => '21:00', 'cross' => 0],
            ['code' => 'S12', 'in' => '14:00', 'out' => '07:30', 'cross' => 1],
            ['code' => 'S15', 'in' => '07:30', 'out' => '20:30', 'cross' => 0],
            ['code' => 'S16', 'in' => '14:00', 'out' => '07:30', 'cross' => 1],
            ['code' => 'S19', 'in' => '07:30', 'out' => '21:00', 'cross' => 0],

            // ===== SPLIT =====
            ['code' => 'S13', 'in' => '07:30', 'out' => '14:00', 'cross' => 0, 'order' => 1],
            ['code' => 'S13', 'in' => '21:00', 'out' => '07:30', 'cross' => 1, 'order' => 2],

            ['code' => 'S14', 'in' => '21:00', 'out' => '07:30', 'cross' => 1, 'order' => 1],
            ['code' => 'S14', 'in' => '07:30', 'out' => '14:00', 'cross' => 0, 'order' => 2],

            ['code' => 'S17', 'in' => '07:30', 'out' => '14:00', 'cross' => 0, 'order' => 1],
            ['code' => 'S17', 'in' => '20:30', 'out' => '07:30', 'cross' => 1, 'order' => 2],

            ['code' => 'S18', 'in' => '20:30', 'out' => '07:30', 'cross' => 1, 'order' => 1],
            ['code' => 'S18', 'in' => '07:30', 'out' => '14:00', 'cross' => 0, 'order' => 2],
        ];

        foreach ($details as $d) {
            DB::table('shift_details')->insert([
                'shift_id' => $shiftIds[$d['code']],
                'clock_in' => $d['in'],
                'clock_out' => $d['out'],
                'is_cross_day' => $d['cross'],
                'order' => $d['order'] ?? 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}