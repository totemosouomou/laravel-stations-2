<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        // シーディング前にテーブルをクリアする
        DB::table('schedules')->delete();
        $id = 1;

        // 現在の時刻を取得
        $now = Carbon::now();

        // 今日の終わりの時刻を設定
        $startOfStartTime = Carbon::tomorrow()->addHours(8)->addMinutes(30);
        $endOfStartTime = Carbon::tomorrow()->addHours(22)->addMinutes(00);

        // ABCの館の数
        $numScreens = 3;

        // 各スクリーンに初期レコードを割り当てる
        $endTimes = [];
        $recentMovieIds = []; // 直近に使用された映画IDを保持する配列
        $usedMovieIds = []; // 使用済みの映画IDを保持する配列

        for ($screenId = 1; $screenId <= $numScreens; $screenId++) {
            // ランダムな作品IDを選択し、重複しないようにする
            do {
                $movieId = rand(1, 10);
            } while (in_array($movieId, $usedMovieIds));
            $usedMovieIds[] = $movieId;

            $startTime = $startOfStartTime->copy()->addMinutes(rand(5, 30));
            $endTime = $startTime->copy()->addMinutes(120);

            DB::table('schedules')->insert([
                'id' => $id++,
                'movie_id' => $movieId,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'created_at' => $now,
                'updated_at' => $now,
                'screen_id' => $screenId,
            ]);

            // 各スクリーンの終了時間と映画IDを保持
            $endTimes[$screenId] = $endTime;
            $recentMovieIds[$screenId] = $movieId;
        }

        while (true) {
            // ①: 現在のscreen_idのend_timeを観測
            asort($endTimes);
            $screenId = key($endTimes);
            $endTime = $endTimes[$screenId];

            // 使用済み映画IDが10件に達した場合にリセット
            if (count($usedMovieIds) >= 10) {
                $usedMovieIds = $recentMovieIds;
            }

            // ②: 使用済みの映画IDを除外し、ランダムなmovie_idを選択
            do {
                $movieId = rand(1, 10);
            } while (in_array($movieId, $recentMovieIds) || in_array($movieId, $usedMovieIds));
            $usedMovieIds[] = $movieId;

            // 要件を満たすまで新たにmovie_idを選択
            $attempts = 0;
            while ($attempts < 10 && Schedule::where('movie_id', $movieId)
                ->where('start_time', '<=', $endTime)
                ->where('end_time', '>=', $endTime)
                ->exists()) {
                do {
                    $movieId = rand(1, 10);
                } while (in_array($movieId, $recentMovieIds) || in_array($movieId, $usedMovieIds));
                $attempts++;
            }

            // ③: start_timeとend_timeを設定してレコードを保存
            $nextStartTime = $endTime->copy()->addMinutes(rand(5, 15));
            $nextEndTime = $nextStartTime->copy()->addMinutes(120);

            // 最終上映が22:00を超えないように調整
            if ($nextStartTime < $endOfStartTime) {
                DB::table('schedules')->insert([
                    'id' => $id++,
                    'movie_id' => $movieId,
                    'start_time' => $nextStartTime,
                    'end_time' => $nextEndTime,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'screen_id' => $screenId,
                ]);

                // 更新された終了時間を保持
                $endTimes[$screenId] = $nextEndTime;
                $recentMovieIds[$screenId] = $movieId; // 最近の映画IDを更新
            } else {
                // 現在のスクリーンがもう上映できない場合は次に進む
                $numScreens--;
                if ($numScreens == 0) {
                    break;
                }

                // 現在のスクリーンの終了時間を最大にして次に進む
                unset($endTimes[$screenId]);
            }
        }
    }
}
