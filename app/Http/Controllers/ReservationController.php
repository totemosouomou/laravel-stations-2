<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateReservationRequest;
use App\Models\Schedule;
use App\Models\Sheet;
use App\Models\Reservation;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function sheets($id, $scheduleId, Request $request)
    {
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        $sheets = Sheet::get();
        $date = $request->query('date');

        // 日付がない場合場合は400エラーを返す
        if (!$date) {
            return response()->json(['error' => 'date parameter is required.'], 400);
        }

        return view('reservation.sheets', ['schedule' => $schedule, 'sheets' => $sheets, 'date' => $date]);
    }

    public function create(Request $request, $id, $scheduleId)
    {
        $sheetId = $request->query('sheetId');
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
        $schedule = Schedule::with('movie')->findOrFail($scheduleId);
        if (!$schedule || $schedule->movie_id != $id) {
            return response()->json(['error' => 'Bad Request: schedule does not belong to the specified movie'], 400);
        }

        // 既に予約されている場合は400エラーを返す
        $existingReservation = Reservation::where('schedule_id', $scheduleId)
            ->where('sheet_id', $sheetId)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_canceled', 0)
            ->exists();
        if (!$sheetId || $existingReservation) {
            return response()->json(['error' => 'Bad Request: Invalid sheetId or reservation already exists for this schedule on the specified date.'], 400);
        }

        return view('reservation.create', ['movie_id' => $id, 'schedule_id' => $scheduleId, 'sheet_id' => $sheetId, 'date' => $date->format('Y-m-d')]);
    }

    public function store(CreateReservationRequest $request)
    {
        $inputs = $request->validated();
        $date = Carbon::parse($inputs['date']);

        DB::beginTransaction();
        try {
            $reservationData = [
                'schedule_id' => $inputs['schedule_id'],
                'sheet_id' => $inputs['sheet_id'],
                'date' => $date->format('Y-m-d'),
                'name' => $inputs['name'],
                'email' => $inputs['email'],
                'is_canceled' => 0,
            ];

            $reservation = Reservation::create($reservationData);
            DB::commit();

            Log::info('Reservation created successfully', ['reservation' => $reservation]);

            return redirect()->route('user.movies.schedules', ['id' => $inputs['movie_id']])->with('success', '予約作成しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create reservation', ['error' => $e->getMessage(), 'inputs' => $inputs]);
            return redirect()->back()->with('error', '予約の作成に失敗しました。');
        }
    }


    // public function create(Request $request, $id, $scheduleId)
    // {
    //     $sheetId = $request->query('sheetId');
    //     $date = $request->query('date');

    //     // 日付またはシートIDがない場合の例外処理
    //     if (!$date || !$sheetId) {
    //         abort(400, 'Bad Request: Both date and sheetId parameters are required');
    //     }

    //     return view('reservation.create', ['movie_id' => $id, 'schedule_id' => $scheduleId, 'sheet_id' => $sheetId, 'date' => $date]);
    // }


    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'movie_id' => 'required|exists:movies,id',
    //         'schedule_id' => 'required|integer|exists:schedules,id',
    //         'sheet_id' => 'required|integer|between:1,15|exists:sheets,id',
    //         'date' => 'required|date_format:Y-m-d',
    //         'name' => 'required|string',
    //         'email' => 'required|email:strict,dns',
    //         'is_canceled' => 'nullable|boolean', // キャンセルフラグはnullableとして許可
    //     ], [
    //         'movie_id.required' => '映画IDを指定してください。',
    //         'movie_id.exists' => '指定された映画IDは存在しません。',
    //         'schedule_id.required' => 'スケジュールIDを指定してください。',
    //         'schedule_id.integer' => 'スケジュールIDには整数を入力してください。',
    //         'schedule_id.exists' => '指定されたスケジュールIDは存在しません。',
    //         'sheet_id.required' => '座席IDを指定してください。',
    //         'sheet_id.integer' => '座席IDには整数を入力してください。',
    //         'sheet_id.between' => '座席IDは1から15の間で指定してください。',
    //         'sheet_id.exists' => '指定された座席IDは存在しません。',
    //         'date.required' => '予約日を指定してください。',
    //         'date.date_format' => '日付の形式が正しくありません。',
    //         'name.required' => '予約者氏名を入力してください。',
    //         'name.string' => '予約者氏名には文字列を入力してください。',
    //         'email.required' => '予約者メールアドレスを入力してください。',
    //         'email.email' => '予約者メールアドレスの形式が正しくありません。',
    //         'is_canceled.boolean' => '不正なリクエストです。',
    //     ]);

    //     if ($validator->fails()) {
    //         return redirect()->back()->withErrors($validator)->withInput();
    //     }

    //     // // キャンセルフラグがtrueの場合、登録をスキップする
    //     // if ($request->input('is_canceled')) {
    //     //     return redirect()->back()->with('error', '不正なリクエストです。')->withInput();
    //     // }

    //     $movieId = $request->input('movie_id');
    //     $scheduleId = $request->input('schedule_id');
    //     $sheetId = $request->input('sheet_id');
    //     $date = $request->input('date');

    //     $existingReservation = Reservation::where('schedule_id', $scheduleId)
    //         ->where('sheet_id', $sheetId)
    //         ->first();

    //     // すでに同じ映画、同じスケジュール、同じ座席に予約があるかチェック
    //     if ($existingReservation && $existingReservation->is_canceled === 0) {
    //         return redirect()->route('user.movies.schedules.sheets', [
    //             'id' => $movieId, 'scheduleId' => $scheduleId, 'date' => $date
    //         ])->with('error', 'その座席はすでに予約済みです。')->withInput();
    //     }

    //     // 予約がキャンセルされている場合、再度予約を作成する
    //     if ($existingReservation && $existingReservation->is_canceled === 1) {
    //         $existingReservation->update([
    //             'name' => $request->input('name'),
    //             'email' => $request->input('email'),
    //             'is_canceled' => 0,
    //         ]);
    //         return redirect()->route('user.movies.schedules', ['id' => $movieId])->with('success', '予約が完了しました。');
    //     }

    //     // 座席予約を完了させる
    //     $schedule = Schedule::with('movie')->findOrFail($scheduleId);
    //     if ($movieId == $schedule->movie_id) {

    //         // 座席予約データを作成
    //         $reservationData = [
    //             'schedule_id' => $scheduleId,
    //             'sheet_id' => $sheetId,
    //             'date' => $date->format('Y-m-d'),
    //             'name' => $request->input('name'),
    //             'email' => $request->input('email'),
    //             'is_canceled' => 0,
    //         ];

    //         // 座席予約を作成
    //         $reservation = Reservation::create($reservationData);
    //         return redirect()->route('user.movies.schedules', ['id' => $movieId])->with('success', '予約が完了しました。');

    //     // 不正なリクエストを弾く
    //     } else {
    //         return redirect()->back()->with('error', '不正なリクエストです。')->withInput();
    //     }
    // }
}
