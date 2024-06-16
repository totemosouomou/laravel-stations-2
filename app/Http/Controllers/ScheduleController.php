<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Requests\CreateScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function movies()
    {
        // スケジュールがある映画を取得
        $movies = Movie::whereHas('schedules', function($query) {
            $query->where('end_time', '>=', now());
        })
        ->with(['schedules' => function($query) {
            $query->where('end_time', '>=', now())
                ->orderBy('start_time', 'asc');
        }])
        ->get();

        // スケジュールを持っていない上映中の映画を取得

        // スケジュールを持っていない映画を取得
        $moviesWithoutSchedules = Movie::doesntHave('schedules')->get();

        return view('schedule.movies', ['movies' => $movies, 'moviesWithoutSchedules' => $moviesWithoutSchedules]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function detail($scheduleId)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with(['movie', 'screen'])->findOrFail($scheduleId);

        return view('schedule.detail', ['schedule' => $schedule]);
    }

    public function auto($date)
    {
        $now = Carbon::now();

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

        return redirect('/admin/schedules')->with('success', 'スケジュールが登録されました。');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $id)
    {
        $movie = Movie::find($id);
        $screens = Screen::all();

        // 指定された日付がある場合、その日付を使用し、ない場合は当日へリダイレクト
        $date = $request->input('date');
        if (!$date) {
            $date = Carbon::today()->format('Y-m-d');
            return redirect()->route('admin.movies.schedules.create', ['id' => $id, 'date' => $date]);
        }
        $currentDate = $date ? Carbon::parse($date) : Carbon::today();

        $diffDays = Carbon::today()->diffInDays(Carbon::parse($date));
        $currentDateTime = Carbon::now()->addDays($diffDays);

        // 上映開始時間の設定
        $openingTime = $date ? Carbon::parse('08:30')->addDays($diffDays) : Carbon::parse('08:30');
        $closingTime = $date ? Carbon::parse('22:00')->addDays($diffDays) : Carbon::parse('22:00');

        $movieDuration = 120; // 映画の上映時間
        $bufferTime = 5; // 準備時間
        $neededTime = $movieDuration + $bufferTime + $bufferTime;

        $availableTimeSlots = [];

        foreach ($screens as $screen) {
            $schedules = $screen->schedules()->whereDate('start_time', $currentDate)->orderBy('start_time')->get();
            $lastEndTime = $currentDateTime->copy()->addMinutes($bufferTime);

            // 現在時刻から15分後以降でないと開始時間を設定しない
            $earliestStartTime = $currentDateTime->copy()->addMinutes(15);

            // 開始時間が営業時間前の場合は、営業時間開始時にリセット
            if ($lastEndTime->lt($openingTime)) {
                $lastEndTime = $openingTime->copy();
            }

            // スケジュールが存在しない場合
            if ($schedules->isEmpty()) {

                // 今日の場合
                if ($currentDateTime->isToday()) {
                    // 開始時間が現在時刻から15分後以降であることを確認
                    if ($lastEndTime->lt($earliestStartTime)) {
                        $lastEndTime = $earliestStartTime->copy();
                    }

                    // 開始時間が営業時間前の場合は、営業時間開始時にリセット
                    if ($lastEndTime->lt($openingTime)) {
                        $lastEndTime = $openingTime->copy();
                    }

                    // 開始時間が現在時刻以降であること、かつ、閉店時間前であることを確認
                    if ($lastEndTime->gte($earliestStartTime) && $lastEndTime->lt($closingTime)) {
                        $availableTimeSlots[] = [
                            'screen_id' => $screen->id,
                            'start_time' => $lastEndTime->copy(),
                            'end_time' => $lastEndTime->copy()->addMinutes($movieDuration)
                        ];
                    }

                // 未来の場合
                } else {
                    // 開始時間を営業時間開始時にリセット
                    $lastEndTime = $openingTime->copy();
                    $availableTimeSlots[] = [
                        'screen_id' => $screen->id,
                        'start_time' => $lastEndTime->copy(),
                        'end_time' => $lastEndTime->copy()->addMinutes($movieDuration)
                    ];
                }

                continue;
            }

            // 各スケジュールの間の空き時間を計算
            for ($i = 0; $i < count($schedules); $i++) {
                $currentSchedule = $schedules[$i];
                $nextSchedule = $schedules[$i + 1] ?? null;

                // 最初のスケジュール前の空き時間を確認
                if ($i == 0 && $currentSchedule->start_time->diffInMinutes($lastEndTime, false) >= $neededTime) {

                    // 営業時間内であることを確認
                    if ($lastEndTime->copy()->gte($openingTime) && $lastEndTime->copy()->addMinutes($movieDuration)->lte($closingTime)) {

                        // 開始時間が最短開始時間以降であることを確認
                        if ($lastEndTime->copy()->gte($earliestStartTime)) {
                            $availableTimeSlots[] = [
                                'screen_id' => $screen->id,
                                'start_time' => $lastEndTime->copy(),
                                'end_time' => $lastEndTime->copy()->addMinutes($movieDuration)
                            ];
                        }
                    }
                }

                // 現在のスケジュールの後に十分な空き時間があるか確認
                if ($nextSchedule && $currentSchedule->end_time->diffInMinutes($nextSchedule->start_time, false) >= $neededTime) {
                    $availableTimeSlots[] = [
                        'screen_id' => $screen->id,
                        'start_time' => $currentSchedule->end_time->copy()->addMinutes($bufferTime),
                        'end_time' => $currentSchedule->end_time->copy()->addMinutes($bufferTime + $movieDuration)
                    ];
                }
            }

            // 最後のスケジュール後の空き時間を確認
            if (!$nextSchedule && $currentSchedule->start_time->addMinutes($neededTime)->isBefore($closingTime)) {
                $availableTimeSlots[] = [
                    'screen_id' => $screen->id,
                    'start_time' => $currentSchedule->end_time->copy()->addMinutes($bufferTime),
                    'end_time' => $currentSchedule->end_time->copy()->addMinutes($bufferTime + $movieDuration)
                ];
            }
        }

        // 他のスクリーンで同時刻に上映されている映画をチェック
        foreach ($availableTimeSlots as $key => $slot) {
            $conflictingSchedules = Schedule::where('start_time', '<', $slot['end_time'])
                ->where('end_time', '>', $slot['start_time'])
                ->where('movie_id', $movie->id)
                ->exists();

            if ($conflictingSchedules) {
                unset($availableTimeSlots[$key]);
            }
        }

        // 空き時間がある場合
        if (count($availableTimeSlots) > 0) {

            // 開始時間を現在時刻に近い順にソート
            usort($availableTimeSlots, function($a, $b) {
                return $a['start_time'] <=> $b['start_time'];
            });
        }

        return view('schedule.create', ['movie' => $movie, 'date' => $date, 'availableTimeSlots' => $availableTimeSlots, 'screens' => $screens]);
    }

    public function store(CreateScheduleRequest $request, $id)
    {
        // 時間の比較を行う
        $startTime = new Carbon("{$request->start_time_date} {$request->start_time_time}");
        $endTime = new Carbon("{$request->end_time_date} {$request->end_time_time}");
        $differenceInMinutes = $endTime->diffInMinutes($startTime);

        $errors = [];

        if ($startTime->eq($endTime)) {
            $errors['start_time_time'] = '開始時間と終了時間が同一です。';
            $errors['end_time_time'] = '開始時間と終了時間が同一です。';
        }

        if ($startTime->gte($endTime)) {
            $errors['start_time_time'] = '開始時間は終了時間より前に設定してください。';
            $errors['end_time_time'] = '開始時間は終了時間より前に設定してください。';
        }

        if ($differenceInMinutes < 6) {
            $errors['start_time_time'] = '開始時間と終了時間の間に5分以上の間隔を空けてください。';
            $errors['end_time_time'] = '開始時間と終了時間の間に5分以上の間隔を空けてください。';
        }

        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        // 日付と時刻を統合
        $start_time = $request->start_time_date . ' ' . $request->start_time_time;
        $end_time = $request->end_time_date . ' ' . $request->end_time_time;

        // 同時刻に同映画が他のスクリーンで上映されているかチェック
        $conflictingMovieSchedules = Schedule::where('start_time', '<', $end_time)
            ->where('end_time', '>', $start_time)
            ->where('movie_id', $id)
            ->exists();

        if ($conflictingMovieSchedules) {
            return redirect()->back()->with('error', 'この時間帯には他のスクリーンで同じ映画が上映されています。');
        }

        // 同時刻に同スクリーンが他の映画で使用されているかチェック
        $conflictingScreenSchedules = Schedule::where('screen_id', $request->input('screen_id'))
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<', $start_time)
                            ->where('end_time', '>', $end_time);
                    });
            })
            ->exists();

        if ($conflictingScreenSchedules) {
            return redirect()->back()->with('error', 'この時間帯にはこのスクリーンで他の映画が上映されています。');
        }

        DB::beginTransaction();

        try {
            // スケジュールデータを作成
            $scheduleData = [
                'movie_id' => $id,
                'screen_id' => $request->input('screen_id'),
                'start_time' => $start_time,
                'end_time' => $end_time,
            ];

            // スケジュールを登録
            $schedule = Schedule::create($scheduleData);

            // 映画IDに対応する映画を取得
            $movie = Movie::findOrFail($id);

            // is_showing が上映予定の場合のみ更新
            if ($movie->is_showing == 0) {
                $movie->update(['is_showing' => 1]);
            }
            DB::commit();

            return redirect('/admin/schedules')->with('success', 'スケジュールが登録されました。');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create schedule' . $e->getMessage());
            return redirect()->back()->with('error', 'スケジュールの登録に失敗しました。');
        }
    }

    public function edit($scheduleId)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        return view('schedule.edit', ['schedule' => $schedule]);
    }

    public function update(UpdateScheduleRequest $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $movieId = $schedule->movie_id;
        $screenId = $schedule->screen_id;

        // 時間の比較を行う
        $startTime = new Carbon("{$request->start_time_date} {$request->start_time_time}");
        $endTime = new Carbon("{$request->end_time_date} {$request->end_time_time}");
        $differenceInMinutes = $endTime->diffInMinutes($startTime);

        $errors = [];

        if ($startTime->eq($endTime)) {
            $errors['start_time_time'] = '開始時間と終了時間が同一です。';
            $errors['end_time_time'] = '開始時間と終了時間が同一です。';
        }

        if ($startTime->gte($endTime)) {
            $errors['start_time_time'] = '開始時間は終了時間より前に設定してください。';
            $errors['end_time_time'] = '開始時間は終了時間より前に設定してください。';
        }

        if ($differenceInMinutes < 6) {
            $errors['start_time_time'] = '開始時間と終了時間の間に5分以上の間隔を空けてください。';
            $errors['end_time_time'] = '開始時間と終了時間の間に5分以上の間隔を空けてください。';
        }

        if (!empty($errors)) {
            return redirect()->back()->withErrors($errors)->withInput();
        }

        // 日付と時刻を統合
        $start_time = $request->start_time_date . ' ' . $request->start_time_time;
        $end_time = $request->end_time_date . ' ' . $request->end_time_time;

        // 同時刻に同映画が他のスクリーンで上映されているかチェック
        $conflictingMovieSchedules = Schedule::where('start_time', '<', $end_time)
            ->where('end_time', '>', $start_time)
            ->where('movie_id', $movieId)
            ->where('id', '!=', $scheduleId)
            ->exists();

        if ($conflictingMovieSchedules) {
            return redirect()->back()->with('error', 'この時間帯には他のスクリーンで同じ映画が上映されています。');
        }

        // 同時刻に同スクリーンが他の映画で使用されているかチェック
        $conflictingScreenSchedules = Schedule::where('screen_id', $screenId)
            ->where('id', '!=', $scheduleId)
            ->where(function ($query) use ($start_time, $end_time) {
                $query->whereBetween('start_time', [$start_time, $end_time])
                    ->orWhereBetween('end_time', [$start_time, $end_time])
                    ->orWhere(function ($query) use ($start_time, $end_time) {
                        $query->where('start_time', '<', $start_time)
                            ->where('end_time', '>', $end_time);
                    });
            })
            ->exists();

        if ($conflictingScreenSchedules) {
            return redirect()->back()->with('error', 'この時間帯にはこのスクリーンで他の映画が上映されています。');
        }

        DB::beginTransaction();

        try {
            $scheduleData = [
                'start_time' => Carbon::parse($startTime),
                'end_time' => Carbon::parse($endTime),
            ];

            $schedule->update($scheduleData);

            // 映画IDに対応する映画を取得
            $movie = Movie::findOrFail($movieId);

            // is_showing が上映予定の場合のみ更新
            if ($movie->is_showing == 0) {
                $movie->update(['is_showing' => 1]);
            }
            DB::commit();

            return redirect()->route('admin.movies.schedules.index')->with('success', 'スケジュールが更新されました。');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to update schedule' . $e->getMessage());
            return redirect()->back()->with('error', 'スケジュールの更新に失敗しました。');
        }
    }

    public function destroy($scheduleId)
    {
        // 指定されたIDのスケジュールを削除
        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->delete();

        return redirect('/admin/schedules')->with('success', 'スケジュールが削除されました。');
    }
}
