<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SheetTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('sheets')->delete();
        $id = 1;
        $sheets = [];

        // スクリーン1 (row = 'a')
        for ($i = 1; $i <= 15; $i++) {
            $sheets[] = [
                'id' => $id++,
                'column' => $i,
                'row' => 'a',
                'screen_id' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // スクリーン2 (row = 'b')
        for ($i = 1; $i <= 15; $i++) {
            $sheets[] = [
                'id' => $id++,
                'column' => $i,
                'row' => 'b',
                'screen_id' => 2,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        // スクリーン3 (row = 'c')
        for ($i = 1; $i <= 15; $i++) {
            $sheets[] = [
                'id' => $id++,
                'column' => $i,
                'row' => 'c',
                'screen_id' => 3,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('sheets')->insert($sheets);
    }
}
