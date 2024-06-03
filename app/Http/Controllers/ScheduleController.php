<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Movie;
use App\Models\Schedule;
use App\Http\Requests\UpdateScheduleRequest;
use Illuminate\Support\Facades\Log;

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

    public function update(UpdateScheduleRequest $request, $scheduleId)
    {
        $schedule = Schedule::findOrFail($scheduleId);
        $movieId = $schedule->movie_id;

        $startTime = new Carbon($request->start_time_time);
        $endTime = new Carbon($request->end_time_time);

        $startTime = $request->input('start_time_date') . ' ' . $request->input('start_time_time');
        $endTime = $request->input('end_time_date') . ' ' . $request->input('end_time_time');

        $schedule->start_time = Carbon::parse($startTime);
        $schedule->end_time = Carbon::parse($endTime);

        $schedule->save();

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
