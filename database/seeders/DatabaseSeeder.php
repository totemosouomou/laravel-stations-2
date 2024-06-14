<?php

namespace Database\Seeders;

use App\Models\Movie;
use App\Models\Screen;
use App\Models\Sheet;
use App\Models\Schedule;
use App\Models\User;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 映画データをシードする
        $this->call(MovieTableSeeder::class);

        // スクリーンデータをシードする
        $this->call(ScreenTableSeeder::class);

        // シートデータをシードする
        $this->call(SheetTableSeeder::class);

        // スケジュールデータをシードする
        $this->call(SchedulesTableSeeder::class);

        // ユーザーデータをシードする
        $this->call(UserTableSeeder::class);
    }
}
