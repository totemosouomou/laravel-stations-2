<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class ReservationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        DB::table('movies')->delete();
        $id = 1;

        $faker = Faker::create();
        $movie = [
            'id' => $id++,
            'title' => 'Movie',
            'image_url' => $faker->imageUrl(),
            'published_year' => $faker->numberBetween(2000, 2024),
            'is_showing' => $faker->boolean(),
            'description' => $faker->text(),
        ];
        DB::table('movies')->insert($movie);

        // 予約するユーザーを作成
        DB::table('users')->delete();
        $id = 1;
        $users = [];
        for ($i = 1; $i <= 5; $i++) {
            $users[] = [
                'id' => $id++,
                'name' => 'User ' . $i,
                'email' => 'user' . $i . '@techtrain.dev',
                'password' => Hash::make('password'),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
        DB::table('users')->insert($users);

        // スクリーンを作成
        DB::table('screens')->delete();
        $id = 1;
        $screenId = DB::table('screens')->insertGetId([
            'id' => $id++,
            'name' => 'A',
        ]);

        // スケジュールを作成
        DB::table('schedules')->delete();
        $id = 1;
        $scheduleId = DB::table('schedules')->insertGetId([
            'id' => $id++,
            'movie_id' => 1,
            'screen_id' => 1,
            'start_time' => Carbon::now()->addDays(1),
            'end_time' => Carbon::now()->addDays(1)->addHours(2),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // シートを作成
        DB::table('sheets')->delete();
        $id = 1;
        $sheetIds = [];
        for ($i = 1; $i < 16; $i++) {
            $sheetIds[] = DB::table('sheets')->insertGetId([
                'id' => $id++,
                'screen_id' => 1,
                'row' => 'A',
                'column' => $i,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }

        // 予約を作成（user_idなし）
        DB::table('reservations')->delete();
        $id = 1;
        $reservations = [];
        $now = Carbon::now();
        foreach ($sheetIds as $index => $sheetId) {
            if ($sheetId >= 11) {
                $userIndex = $sheetId;
            } else {
                $userIndex = ($index % 5) + 1;
            }

            $reservations[] = [
                'id' => $id++,
                'date' => $now->format('Y-m-d'),
                'schedule_id' => $scheduleId,
                'sheet_id' => $sheetId,
                'email' => 'user' . $userIndex . '@techtrain.dev',
                'name' => 'User ' . $userIndex,
                'is_canceled' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('reservations')->insert($reservations);
    }
}
