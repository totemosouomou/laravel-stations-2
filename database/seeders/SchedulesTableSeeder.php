<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Movie;
use App\Models\Schedule;
use Carbon\Carbon;

class SchedulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 現在の時刻を取得
        $now = Carbon::now();

        // 今日の終わりの時刻を設定
        $endOfDay = Carbon::today()->addHours(23)->addMinutes(59);

        // 本日の現時刻以降のスケジュールを追加
        // 最初の上映は現時刻から10分後に開始
        // 開始から120分後に終了
        // 次の上映は前の上映終了後に開始
        // 前の上映終了後10分から30分後に開始
        // 最終上映は、23:59までにend_timeを迎える必要がある（超える場合は上映できない）
        // 上記のスケジュールで、いずれか1つの作品をランダムで選び、上映する

        // 全ての映画を取得
        $movies = Movie::all();

        // 最初の上映時間を設定
        $startTime = $now->copy()->addMinutes(10);

        while ($startTime->copy()->addMinutes(120)->lessThanOrEqualTo($endOfDay)) {
            // 上映終了時間を設定
            $endTime = $startTime->copy()->addMinutes(120);

            // 最終上映時間が23:59以降になる場合はスケジュールを生成しない
            if ($endTime->greaterThanOrEqualTo($endOfDay)) {
                break;
            }

            // ランダムな映画を選択
            $selectedMovie = $movies->random();

            // スケジュールを生成
            Schedule::create([
                'movie_id' => $selectedMovie->id,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ]);

            // 次の上映開始時間を設定
            $startTime = $endTime->copy()->addMinutes(rand(10, 30));
        }
    }
}
