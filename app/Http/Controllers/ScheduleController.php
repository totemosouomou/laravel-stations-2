<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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
        $movies = Movie::has('schedules')->with('schedules')->get();
        return view('schedule.movies', ['movies' => $movies]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function detail($scheduleId)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        return view('schedule.detail', ['schedule' => $schedule]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        $movie = Movie::find($id);
        $screens = Screen::all();

        $currentDateTime = Carbon::now();
        $movieDuration = 120;  // 映画の上映時間
        $endOfStartTime = Carbon::tomorrow()->addHours(22)->addMinutes(00);
        $neededTime = $currentDateTime->copy()->addMinutes($movieDuration + 20);
        $movieIds = [];
        $screenToUse = null;
        $startTime = null;
        $endTime = null;

        // 現在時刻から140分後までスケジュールがないスクリーンを探すクエリ
        $availableScreens = Screen::with('schedules')->whereDoesntHave('schedules', function ($query) use ($currentDateTime, $neededTime) {
            $query->where(function ($query) use ($currentDateTime, $neededTime) {
                $query->whereBetween('start_time', [$currentDateTime, $neededTime])
                    ->orWhereBetween('end_time', [$currentDateTime, $neededTime])
                    ->orWhere(function ($query) use ($currentDateTime, $neededTime) {
                        $query->where('start_time', '<', $currentDateTime)
                            ->where('end_time', '>', $currentDateTime->copy()->addMinutes($neededTime));
                    });
            });
        })->get();

        // スケジュールがあるスクリーンを探すクエリ
        $inScheduleScreens = Screen::with('schedules')->whereHas('schedules', function ($query) use ($currentDateTime, $neededTime) {
            $query->where(function ($query) use ($currentDateTime, $neededTime) {
                $query->whereBetween('start_time', [$currentDateTime, $neededTime])
                    ->orWhereBetween('end_time', [$currentDateTime, $neededTime])
                    ->orWhere(function ($query) use ($currentDateTime, $neededTime) {
                        $query->where('start_time', '<', $currentDateTime)
                            ->where('end_time', '>', $neededTime);
                    });
            });
        })->get();

        // 準備時間15分で上映開始するための条件チェック
        if($availableScreens->count() > 0) {

            // 各スケジュールに含まれる上映中の作品を配列にする
            foreach ($inScheduleScreens as $screen) {
                foreach ($screen->schedules as $schedule) {
                    $movieIds[] = $schedule->movie_id;
                }
            }

            // 上映終了までの時間に上映作品が重ならないことを確認
            foreach ($availableScreens as $screen) {
                if (in_array($id, $movieIds)) {
                    continue;
                }

                $screenToUse = Screen::find($screen->id);
                $startTime = $currentDateTime->copy()->addMinutes(15);
                $endTime = $startTime->copy()->addMinutes($movieDuration);
                break;
            }

        // スケジュールの後ろに挿入
        } else {

            // スクリーンごとの終了時間と映画IDを保持する配列
            $endTimes = [];
            $recentMovieIds = [];

            // スクリーンごとの終了時間と映画IDを設定
            foreach ($screens as $screen) {
                $lastSchedule = $screen->schedules()->orderBy('end_time', 'desc')->first();
                $endTimes[$screen->id] = $lastSchedule ? $lastSchedule->end_time : Carbon::minValue();
                $recentMovieIds[$screen->id] = $lastSchedule ? $lastSchedule->movie_id : null;
            }

            // 終了時間の配列を現在時刻に近い順にソートする
            uasort($endTimes, function($a, $b) {
                return $a->lessThan($b) ? -1 : 1;
            });

            // 最も現在時刻に近い終了時間を取得
            reset($endTimes);
            $nearestEndTime = current($endTimes);

            // 終了時間に基づいてスクリーンを選択
            foreach ($endTimes as $screenId => $endTime) {
                $lastSchedule = Schedule::where('screen_id', $screenId)->orderBy('end_time', 'desc')->first();

                // 現在時刻より15分前までに登録するかどうかで準備時間を変更
                if ($lastSchedule && $lastSchedule->end_time->addMinutes(15)->isBefore($currentDateTime)) {
                    $screenToUse = Screen::find($screenId);
                    $startTime = $lastSchedule->end_time->addMinutes(5);
                    $endTime = $startTime->copy()->addMinutes(120);
                    break;
                } elseif ($lastSchedule) {
                    $screenToUse = Screen::find($screenId);
                    $startTime = $lastSchedule->end_time->addMinutes(15);
                    $endTime = $startTime->copy()->addMinutes(120);
                    break;
                }
            }
        }

        // 利用可能なスクリーンがない場合の処理
        if (!$screenToUse || $startTime->isAfter($endOfStartTime)) {
            return response()->json(['message' => 'No available screens or scheduling exceeds allowed today'], 422);
        }

        // 開始時間と終了時間を日付と時間に分割
        $startDate = $startTime->format('Y-m-d');
        $startTimePart = $startTime->format('H:i');
        $endDate = $endTime->format('Y-m-d');
        $endTimePart = $endTime->format('H:i');

        // Bladeテンプレートにデータを渡す
        return view('schedule.create', [
            'movie' => $movie,
            'screens' => $screens,
            'screenToUse' => $screenToUse->id,
            'startDate' => $startDate,
            'startTime' => $startTimePart,
            'endDate' => $endDate,
            'endTime' => $endTimePart,
        ]);
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

        // スケジュールデータを作成
        $scheduleData = [
            'movie_id' => $id,
            'screen_id' => $request->input('screen_id'),
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];

        // スケジュールを作成
        $schedule = Schedule::create($scheduleData);

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

        // バリデーション
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'start_time_date' => 'required|date_format:Y-m-d|before_or_equal:end_time_date',
            'start_time_time' => 'required|date_format:H:i',
            'end_time_date' => 'required|date_format:Y-m-d|after_or_equal:start_time_date',
            'end_time_time' => 'required|date_format:H:i',
        ], [
            'movie_id.required' => '映画IDを指定してください。',
            'movie_id.exists' => '指定された映画IDは存在しません。',
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

        // スケジュールデータを更新
        $scheduleData = [
            'start_time' => Carbon::parse($startTime),
            'end_time' => Carbon::parse($endTime),
        ];

        $schedule->update($scheduleData);

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
