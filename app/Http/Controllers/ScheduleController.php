<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Movie;
use App\Models\Schedule;

class ScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin()
    {
        // スケジュールがある映画を取得
        $movies = Movie::has('schedules')->with('schedules')->get();
        return view('schedule.movie', ['movies' => $movies]);
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
        // 指定されたIDの映画を取得
        $movie = Movie::findOrFail($id);
        return view('schedule.create', ['movie' => $movie]);
    }

    public function edit($scheduleId)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        return view('schedule.edit', ['schedule' => $schedule]);
    }

    public function store(Request $request, $id)
    {
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

        // スケジュールデータを作成
        $scheduleData = [
            'movie_id' => $id,
            'start_time' => $start_time,
            'end_time' => $end_time,
        ];

        // スケジュールを作成
        $schedule = Schedule::create($scheduleData);

        return redirect('/admin/schedules')->with('success', 'スケジュールが登録されました！');
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

        return redirect()->route('admin.schedules.index');
    }

    public function destroy($scheduleId)
    {
        // 指定されたIDのスケジュールを削除
        $schedule = Schedule::findOrFail($scheduleId);
        $schedule->delete();

        return redirect('/admin/schedules')->with('success', 'スケジュールが削除されました');
    }
}
