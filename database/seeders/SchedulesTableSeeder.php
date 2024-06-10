<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;
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

        $now = Carbon::now();

        // いつのスケジュールを登録するか設定する
        $date = $now->addDays(1)->format('Y-m-d');

        // 指定された日付を取得
        $startOfStartTime = Carbon::parse($date)->startOfDay()->addHours(8)->addMinutes(30);
        $endOfStartTime = Carbon::parse($date)->startOfDay()->addHours(22)->addMinutes(00);

        // スクリーン数
        $numScreens = Screen::count();

        // 上映中の映画のIDを取得
        $is_showingIds = Movie::where('is_showing', 1)->pluck('id')->toArray();
        $numMovies = count($is_showingIds);

        // 各スクリーンに初期レコードを割り当てる
        $endTimes = [];
        $recentMovieIds = [];
        $usedMovieIds = [];

        for ($screenId = 1; $screenId <= $numScreens; $screenId++) {
            // 現在の日付のスケジュールを取得し、空いている時間に追加する
            $schedules = Schedule::where('screen_id', $screenId)
                ->whereDate('start_time', $startOfStartTime->format('Y-m-d'))
                ->orderBy('start_time')
                ->get();

            if ($schedules->isEmpty()) {
                // 使用可能な映画IDをランダムに選択し、重複しないようにする
                do {
                    $availableMovieIds = array_diff($is_showingIds, $usedMovieIds);
                    $movieId = $availableMovieIds[array_rand($availableMovieIds)];
                } while (in_array($movieId, $recentMovieIds));

                $usedMovieIds[] = $movieId;

                $startTime = $startOfStartTime->copy()->addMinutes(rand(15, 30));
                $endTime = $startTime->copy()->addMinutes(120);

                // スケジュールデータを作成
                $scheduleData = [
                    'id' => $id++,
                    'movie_id' => $movieId,
                    'screen_id' => $screenId,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ];

                // スケジュールを作成
                Schedule::create($scheduleData);

                // 各スクリーンの終了時間と映画IDを保持
                $endTimes[$screenId] = $endTime;
                $recentMovieIds[$screenId] = $movieId;
            } else {
                // 既存のスケジュールがある場合は終了時間を取得
                $endTimes[$screenId] = $schedules->last()->end_time;
            }
        }

        while (true) {
            // ①: 現在のscreen_idのend_timeを観測
            asort($endTimes);
            $screenId = key($endTimes);
            $endTime = $endTimes[$screenId];

            // 使用済み映画IDがすべての上映中映画のIDに達した場合にリセット
            if (count($usedMovieIds) >= $numMovies) {
                $usedMovieIds = $recentMovieIds;
            }

            // ②: 使用済みの映画IDを除外し、ランダムなmovie_idを選択
            do {
                $availableMovieIds = array_diff($is_showingIds, $usedMovieIds);
                $movieId = $availableMovieIds[array_rand($availableMovieIds)];
            } while (in_array($movieId, $recentMovieIds));

            $usedMovieIds[] = $movieId;

            // 要件を満たすまで新たにmovie_idを選択
            $attempts = 0;
            while ($attempts < $numMovies && Schedule::where('movie_id', $movieId)
                ->where('start_time', '<=', $endTime)
                ->where('end_time', '>=', $endTime)
                ->exists()) {
                do {
                    $availableMovieIds = array_diff($is_showingIds, $usedMovieIds);
                    $movieId = $availableMovieIds[array_rand($availableMovieIds)];
                } while (in_array($movieId, $recentMovieIds));
                $attempts++;
            }

            // ③: start_timeとend_timeを設定してレコードを保存
            $nextStartTime = $endTime->copy()->addMinutes(rand(5, 15));
            $nextEndTime = $nextStartTime->copy()->addMinutes(120);

            // 最終上映が22:00を超えないように調整
            if ($nextStartTime < $endOfStartTime) {

                // スケジュールデータを作成
                $scheduleData = [
                    'id' => $id++,
                    'movie_id' => $movieId,
                    'screen_id' => $screenId,
                    'start_time' => $nextStartTime,
                    'end_time' => $nextEndTime,
                ];

                // スケジュールを作成
                Schedule::create($scheduleData);

                // 更新された終了時間を保持
                $endTimes[$screenId] = $nextEndTime;
                $recentMovieIds[$screenId] = $movieId; // 最近の映画IDを更新

            // 現在のスクリーンがもう上映できない場合は次に進む
            } else {
                unset($endTimes[$screenId]);
                $numScreens--;
                if ($numScreens == 0) {
                    break;
                }
            }
        }
    }
}
