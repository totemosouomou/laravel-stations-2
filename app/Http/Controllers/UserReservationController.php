<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests\CreateReservationRequest;
use App\Models\Schedule;
use App\Models\Sheet;
use App\Models\Reservation;
use Carbon\Carbon;

class UserReservationController extends Controller
{
    public function sheets(Request $request, $id, $scheduleId)
    {
        $schedule = Schedule::with('movie', 'reservations')->findOrFail($scheduleId);
        $sheets = Sheet::get();
        $dateString = $request->query('date');

        // 日付がない場合の例外処理
        if (!$dateString) {
            return response()->json(['error' => 'Bad Request: date parameter is required'], 400);
        }

        // 日付文字列をCarbonオブジェクトに変換
        try {
            $date = new Carbon($dateString);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Bad Request: invalid date format'], 400);
        }

        // スケジュールが指定された映画に関連しているか確認
        if (!$schedule || $schedule->movie_id != $id) {
            return response()->json(['error' => 'Bad Request: schedule does not belong to the specified movie'], 400);
        }

        // 指定された日付とスケジュールの予約を取得
        $reservations = Reservation::where('schedule_id', $scheduleId)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_canceled', 0)
            ->get();

        return view('user.reservation.sheets', ['schedule' => $schedule, 'sheets' => $sheets, 'reservations' => $reservations]);
    }

    public function create(Request $request, $id, $scheduleId)
    {
        // 指定されたスケジュールを取得
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);

        // クエリパラメータを取得
        $sheetId = $request->query('sheetId');
        $dateString = $request->query('date');

        // 座席IDがない場合の例外処理
        if (!$sheetId) {
            return response()->json(['error' => 'Bad Request: sheetId parameter is required'], 400);
        }

        // 日付がない場合の例外処理
        if (!$dateString) {
            return response()->json(['error' => 'Bad Request: date parameter is required'], 400);
        }

        // 日付文字列をCarbonオブジェクトに変換
        try {
            $date = new Carbon($dateString);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Bad Request: invalid date format'], 400);
        }

        // スケジュールが指定された映画に関連しているか確認
        if (!$schedule || $schedule->movie_id != $id) {
            return response()->json(['error' => 'Bad Request: schedule does not belong to the specified movie'], 400);
        }

        // 既に予約されている場合は400エラーを返す
        $existingReservation = Reservation::where('schedule_id', $scheduleId)
            ->where('sheet_id', $sheetId)
            ->where('is_canceled', 0)
            ->exists();
        if ($existingReservation) {
            return response()->json(['error' => 'Bad Request: Reservation already exists for this schedule on the specified date.'], 400);
        }

        return view('user.reservation.create', ['schedule_id' => $scheduleId, 'sheet_id' => $sheetId]);
    }

    public function store(CreateReservationRequest $request)
    {
        $inputs = $request->validated();
        $date = new Carbon($request->input('date'));

        // 既に予約されている場合
        $existingReservation = Reservation::where('schedule_id', $inputs['schedule_id'])
            ->where('sheet_id', $inputs['sheet_id'])
            ->where('is_canceled', 0)
            ->exists();
        if ($existingReservation) {
            $redirectUrl = '/movies/' . $request->input('movie_id') . '/schedules/' . $inputs['schedule_id'] . '/sheets?date=' . $inputs['date'];
            return redirect($redirectUrl)->with('error', 'その座席はすでに予約済みです。');
        }

        DB::beginTransaction();
        try {
            $reservationData = [
                'schedule_id' => $inputs['schedule_id'],
                'sheet_id' => $inputs['sheet_id'],
                'date' => Carbon::parse($date),
                'name' => $inputs['name'],
                'email' => $inputs['email'],
                'is_canceled' => 0,
            ];

            $reservation = Reservation::create($reservationData);
            DB::commit();

            $movieId = Schedule::findOrFail($inputs['schedule_id'])->movie_id;

            return redirect()->route('user.movies.schedules', ['id' => $movieId])->with('success', '予約が完了しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create reservation', ['error' => $e->getMessage(), 'inputs' => $inputs]);
            return redirect()->back()->with('error', '予約の作成に失敗しました。');
        }
    }
}
