<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Screen;

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
        $movies = Movie::has('schedules')->with(['schedules' => function($query) {
            $query->orderBy('start_time', 'asc');
        }])
        ->get();

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

    public function store(Request $request, $id)
    {
        // リクエストに映画IDをマージ
        $request->merge(['movie_id' => $id]);

        // バリデーション
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'screen_id' => 'required|exists:screens,id',
            'start_time_date' => 'required|date_format:Y-m-d|before_or_equal:end_time_date',
            'start_time_time' => 'required|date_format:H:i',
            'end_time_date' => 'required|date_format:Y-m-d|after_or_equal:start_time_date',
            'end_time_time' => 'required|date_format:H:i',
        ], [
            'movie_id.required' => '映画IDを指定してください。',
            'movie_id.exists' => '指定された映画IDは存在しません。',
            'screen_id.required' => 'スクリーンIDを指定してください。',
            'screen_id.exists' => '指定されたスクリーンIDは存在しません。',
            'start_time_date.required' => '開始日を指定してください。',
            'start_time_date.date_format' => '開始日の形式が正しくありません。',
            'start_time_date.before_or_equal' => '開始日は終了日以前の日付を指定してください。',
            'start_time_time.required' => '開始時間を指定してください。',
            'start_time_time.date_format' => '開始時間の形式が正しくありません。',
            'end_time_date.required' => '終了日を指定してください。',
            'end_time_date.date_format' => '終了日の形式が正しくありません。',
            'end_time_date.after_or_equal' => '終了日は開始日以降の日付を指定してください。',
            'end_time_time.required' => '終了時間を指定してください。',
            'end_time_time.date_format' => '終了時間の形式が正しくありません。',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

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

        // スケジュールデータを作成
        $scheduleData = [
            'movie_id' => $id,
            'screen_id' => $request->input('screen_id'),
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];

        // スケジュールを作成
        $schedule = Schedule::create($scheduleData);

        // 映画IDに対応する映画を取得
        $movie = Movie::findOrFail($id);

        // is_showing が上映予定の場合のみ更新
        if ($movie->is_showing == 0) {
            $movie->update(['is_showing' => 1]);
        }

        return redirect('/admin/schedules')->with('success', 'スケジュールが登録されました。');
    }

    public function edit($scheduleId)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        return view('schedule.edit', ['schedule' => $schedule]);
    }

    public function update(Request $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $movieId = $schedule->movie_id;
        $screenId = $schedule->screen_id;

        // バリデーション
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'screen_id' => 'required|exists:screens,id',
            'start_time_date' => 'required|date_format:Y-m-d|before_or_equal:end_time_date',
            'start_time_time' => 'required|date_format:H:i',
            'end_time_date' => 'required|date_format:Y-m-d|after_or_equal:start_time_date',
            'end_time_time' => 'required|date_format:H:i',
        ], [
            'movie_id.required' => '映画IDを指定してください。',
            'movie_id.exists' => '指定された映画IDは存在しません。',
            'screen_id.required' => 'スクリーンIDを指定してください。',
            'screen_id.exists' => '指定されたスクリーンIDは存在しません。',
            'start_time_date.required' => '開始日を指定してください。',
            'start_time_date.date_format' => '開始日の形式が正しくありません。',
            'start_time_date.before_or_equal' => '開始日は終了日以前の日付を指定してください。',
            'start_time_time.required' => '開始時間を指定してください。',
            'start_time_time.date_format' => '開始時間の形式が正しくありません。',
            'end_time_date.required' => '終了日を指定してください。',
            'end_time_date.date_format' => '終了日の形式が正しくありません。',
            'end_time_date.after_or_equal' => '終了日は開始日以降の日付を指定してください。',
            'end_time_time.required' => '終了時間を指定してください。',
            'end_time_time.date_format' => '終了時間の形式が正しくありません。',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

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
            ->exists();

        if ($conflictingMovieSchedules) {
            return redirect()->back()->with('error', 'この時間帯には他のスクリーンで同じ映画が上映されています。');
        }

        // 同時刻に同スクリーンが他の映画で使用されているかチェック
        $conflictingScreenSchedules = Schedule::where('screen_id', $screenId)
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

        // スケジュールデータを更新
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

        return redirect()->route('admin.movies.schedules.index');
    }

    public function destroy($scheduleId)
    {
        // 指定されたIDのスケジュールを削除
        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->delete();

        return redirect('/admin/schedules')->with('success', 'スケジュールが削除されました。');
    }
}
