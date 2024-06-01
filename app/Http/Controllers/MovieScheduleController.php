<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Movie;
use App\Models\Schedule;
use App\Http\Requests\UpdateScheduleRequest;

class MovieScheduleController extends Controller
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
    public function Schedule($id)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($id);
        return view('schedule.schedule', ['schedule' => $schedule]);
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

    public function edit($id)
    {
        // 指定されたIDのスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($id);

        return view('schedule.edit', ['schedule' => $schedule]);
    }

    public function store(Request $request, $id)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'movie_id' => 'required|exists:movies,id',
            'start_time_date' => 'required|date_format:Y-m-d',
            'start_time_time' => 'required|date_format:H:i',
            'end_time_date' => 'required|date_format:Y-m-d',
            'end_time_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 302);
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

    // public function update(Request $request, $scheduleId)
    // {
    //     // バリデーション
    //     $validator = Validator::make($request->all(), [
    //         'movie_id' => 'required|exists:movies,id',
    //         'schedule_id' => 'required|exists:schedules,id',
    //         'start_time_date' => 'required|date_format:Y-m-d|before_or_equal:end_time_date',
    //         'start_time_time' => 'required|date_format:H:i|before:end_time_time',
    //         'end_time_date' => 'required|date_format:Y-m-d|after_or_equal:start_time_date',
    //         'end_time_time' => 'required|date_format:H:i|after:start_time_time',

    //         // 12時間以内であることを検証
    //         'end_time' => [
    //             'required',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 $startTime = new Carbon($request->start_time_time);
    //                 $endTime = new Carbon($request->end_time_time);
    //                 $differenceInHours = $endTime->diffInHours($startTime);
    //                 if ($differenceInHours > 12) {
    //                     $fail($attribute . 'は12時間以内で設定してください。');
    //                 }
    //             },
    //         ],

    //         // 5分以上の間隔があることを検証
    //         'start_time' => [
    //             'required',
    //             function ($attribute, $value, $fail) use ($request) {
    //                 $startTime = new Carbon($request->start_time_time);
    //                 $endTime = new Carbon($request->end_time_time);
    //                 $differenceInMinutes = $endTime->diffInMinutes($startTime);
    //                 if ($differenceInMinutes < 5) {
    //                     $fail($attribute . 'と終了時刻との間に5分以上の間隔を開けてください。');
    //                 }
    //             },
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         $errors = $validator->errors();
    //         return redirect()->route('schedule.movies.edit', $scheduleId)->withErrors($errors)->withInput();
    //     }

    //     // 日付と時刻を統合
    //     // $start_time = $request->start_time_date . ' ' . $request->start_time_time;
    //     // $end_time = $request->end_time_date . ' ' . $request->end_time_time;
    //     $startTime = $request->input('start_time_date') . ' ' . $request->input('start_time_time');
    //     $endTime = $request->input('end_time_date') . ' ' . $request->input('end_time_time');

    //     $schedule->start_time = Carbon::parse($startTime);
    //     $schedule->end_time = Carbon::parse($endTime);

    //     // スケジュールデータを作成
    //     $scheduleData = [
    //         // 'movie_id' => $request->input('movie_id'),
    //         'start_time' => $start_time,
    //         'end_time' => $end_time,
    //     ];

    //     // 指定されたIDのスケジュールを更新
    //     $schedule = Schedule::findOrFail($scheduleId);
    //     $schedule->update($scheduleData);

    //     return redirect()->route('admin.movies.detail', $request->movie_id)->with('success', 'スケジュールを更新しました');
    // }

    public function destroy($id)
    {
        // 指定されたIDのスケジュールを削除
        $schedule = Schedule::findOrFail($id);
        $schedule->delete();

        return redirect('/admin/schedules')->with('success', 'スケジュールが削除されました');
    }


public function update(UpdateScheduleRequest $request, $scheduleId)
{
    // 指定されたIDのスケジュールを取得
    $schedule = Schedule::findOrFail($scheduleId);

    // 日付と時刻を統合してCarbonインスタンスを作成
    $startTime = new Carbon($request->start_time_time);
    $endTime = new Carbon($request->end_time_time);
    $startTime = Carbon::parse($request->input('start_time_date') . ' ' . $request->input('start_time_time'));
    $endTime = Carbon::parse($request->input('end_time_date') . ' ' . $request->input('end_time_time'));

    // 開始時間が終了時間と同じかそれより後であってはならない
    if($startTime->gte($endTime)) {
        return redirect()
            ->route('schedule.movies.edit', ['scheduleId' => $scheduleId])
            ->withErrors(['time_error' => '時間の設定を確認してください。']);
    }

    // 12時間以内であることを検証
    $differenceInHours = $endTime->diffInHours($startTime);
    if ($differenceInHours > 12) {
        return redirect()
            ->route('schedule.movies.edit', ['scheduleId' => $scheduleId])
            ->withErrors(['time_error' => '12時間以内に設定してください。']);
    }

    // 間隔は5分以上あける
    $diffInMinutes = $endTime->diffInMinutes($startTime);
    if ($diffInMinutes < 5) {
        return redirect()
            ->route('schedule.movies.edit', ['scheduleId' => $scheduleId])
            ->withErrors(['time_error' => '5分以上間隔をあけてください。']);
    }

    // スケジュールデータを作成
    $scheduleData = [
        'start_time' => $startTime,
        'end_time' => $endTime,
    ];

    // 指定されたIDのスケジュールを更新
    $schedule->update($scheduleData);

    return redirect()->route('admin.movies.detail', $schedule->movie_id)->with('success', 'スケジュールを更新しました');
}


}