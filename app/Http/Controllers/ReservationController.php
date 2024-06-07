<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreateAdminReservationRequest;
use App\Http\Requests\UpdateAdminReservationRequest;
use App\Models\Schedule;
use App\Models\Sheet;
use App\Models\Reservation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function movies()
    {
        $reservations = Reservation::with(['schedule.movie', 'sheet'])
            ->whereHas('schedule', function ($query) {
                $query->where('end_time', '>', Carbon::now());
            })
            ->get();

        return view('reservation.movies', ['reservations' => $reservations]);
    }

    public function create(Request $request)
    {
        // クエリパラメータを取得
        $scheduleId = $request->query('schedule_id');
        $dateString = $request->query('date');
        $sheetId = $request->query('sheet_id');
        $sheets = Sheet::get();

        // スケジュールの選択肢を条件に基づいて取得
        $schedules = Schedule::with('movie', 'reservations')->where('end_time', '>', Carbon::now())
        ->orderBy('start_time')
        ->get()
        ->map(function ($schedule) {
            $schedule->start_time = Carbon::parse($schedule->start_time)->format('Y-m-d H:i:00');
            $schedule->end_time = Carbon::parse($schedule->end_time)->format('Y-m-d H:i:00');
            return $schedule;
        })
        ->groupBy(function ($schedule) {
            return Carbon::parse($schedule->start_time)->format('Y-m-d');
        });

        if ($scheduleId && $dateString) {
            $movieId = Schedule::findOrFail($scheduleId)->movie_id;
            $startTime = Schedule::findOrFail($scheduleId)->start_time;

            // 日付文字列をCarbonオブジェクトに変換
            try {
                $date = new Carbon($dateString);
                $start_date = new Carbon($startTime);
            } catch (\Exception $e) {
                return redirect()->route('admin.reservations.index')->with('error', '予約の作成に失敗しました。');
            }

            // 不正アクセス対策
            if ($date->format('Y-m-d') !== $start_date->format('Y-m-d')) {
                return redirect()->route('admin.reservations.index')->with('error', '予約の作成に失敗しました。');
            }

            // 指定された日付とスケジュールの予約を取得
            $reservations = Reservation::where('schedule_id', $scheduleId)
                ->where('date', $date->format('Y-m-d'))
                ->where('is_canceled', 0)
                ->get();

            return view('reservation.create', ['movie_id' => $movieId, 'schedule_id' => $scheduleId, 'sheet_id' => $sheetId ? $sheetId : '', 'schedules' => $schedules, 'sheets' => $sheets, 'date' => $date->format('Y-m-d'), 'reservations' => $reservations]);

        } else {
            return view('reservation.create', ['movie_id' => '','schedule_id' => '', 'date' => '', 'sheet_id' => $sheetId, 'schedules' => $schedules, 'sheets' => $sheets, 'reservations' => '']);

        }
    }

    public function store(CreateAdminReservationRequest $request)
    {
        $inputs = $request->validated();
        $date = Carbon::parse($request->input('date'));

        // 既に予約されている場合
        $existingReservation = Reservation::where('schedule_id', $inputs['schedule_id'])
            ->where('sheet_id', $inputs['sheet_id'])
            ->where('is_canceled', 0)
            ->exists();
        if ($existingReservation) {
            $redirectUrl = '/admin/reservations/create?schedule_id=' . $inputs['schedule_id'] . '&date=' . $date->format('Y-m-d');
            return redirect($redirectUrl)->with('error', '予約がある座席のためお取りできませんでした。');
        }

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

            return redirect()->route('admin.reservations.index')->with('success', '予約を作成しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create reservation', ['error' => $e->getMessage(), 'inputs' => $inputs]);
            return redirect()->route('admin.reservations.index')->with('error', '予約の作成に失敗しました。');
        }
    }

    public function edit(Request $request, $reservationId)
    {
        // 指定された予約IDを取得
        $myReservation = Reservation::with('schedule.movie')->findOrFail($reservationId);

        // スケジュールの選択肢を条件に基づいて取得
        $schedules = Schedule::with('movie', 'reservations')->where('end_time', '>', Carbon::now())
        ->orderBy('start_time')
        ->get()
        ->map(function ($schedule) {
            $schedule->start_time = Carbon::parse($schedule->start_time)->format('Y-m-d H:i:00');
            $schedule->end_time = Carbon::parse($schedule->end_time)->format('Y-m-d H:i:00');
            return $schedule;
        })
        ->groupBy(function ($schedule) {
            return Carbon::parse($schedule->start_time)->format('Y-m-d');
        });

        // クエリパラメータを取得
        $scheduleId = $request->query('schedule_id');
        $dateString = $request->query('date');

        $sheets = Sheet::get();

        // クエリパラメータがない場合
        if (is_null($scheduleId) && is_null($dateString)) {
            return view('reservation.detail', ['myReservation' => $myReservation, 'schedules' => $schedules, 'sheets' => $sheets]);
        }

        $sheetId = $request->query('sheet_id');
        $movieId = Schedule::findOrFail($scheduleId)->movie_id;
        $startTime = Schedule::findOrFail($scheduleId)->start_time;

        // 日付文字列をCarbonオブジェクトに変換
        try {
            $date = new Carbon($dateString);
            $start_date = new Carbon($startTime);
        } catch (\Exception $e) {
            return redirect()->route('admin.reservations.index')->with('error', '予約の作成に失敗しました。');
        }

        // 不正アクセス対策
        if ($date->format('Y-m-d') !== $start_date->format('Y-m-d')) {
            return redirect()->route('admin.reservations.index')->with('error', '予約の作成に失敗しました。');
        }

        // 指定された日付とスケジュールの予約を取得
        $reservations = Reservation::where('schedule_id', $scheduleId)
            ->where('date', $date->format('Y-m-d'))
            ->where('is_canceled', 0)
            ->get();

        return view('reservation.edit', ['reservationId' => $reservationId, 'myReservation' => $myReservation, 'movie_id' => $movieId, 'schedule_id' => $scheduleId, 'sheet_id' => $sheetId ? $sheetId : '', 'schedules' => $schedules, 'sheets' => $sheets, 'date' => $date->format('Y-m-d'), 'reservations' => $reservations]);
    }

    public function update(UpdateAdminReservationRequest $request, $reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $inputs = $request->validated();

        // 既に予約されている場合
        $existingReservation = Reservation::where('schedule_id', $inputs['schedule_id'])
            ->where('sheet_id', $inputs['sheet_id'])
            ->where('is_canceled', 0)
            ->exists();
        if ($existingReservation) {
            $redirectUrl = '/admin/reservations/' . $reservationId . '/edit?schedule_id=' . $inputs['schedule_id'];
            return redirect($redirectUrl)->with('error', '予約がある座席のためお取りできませんでした。');
        }

        DB::beginTransaction();
        try {
            $reservationData = [
                'schedule_id' => $inputs['schedule_id'],
                'sheet_id' => $inputs['sheet_id'],
                'name' => $inputs['name'],
                'email' => $inputs['email'],
            ];

            $reservation->update($reservationData);
            DB::commit();

            return redirect()->route('admin.reservations.index')->with('success', '予約を編集しました。');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to create reservation', ['error' => $e->getMessage(), 'inputs' => $inputs]);
            return redirect()->route('admin.reservations.index')->with('error', '予約の編集に失敗しました。');
        }
    }

    public function destroy($reservationId)
    {
        $reservation = Reservation::findOrFail($reservationId);
        $reservation->delete();

        return redirect()->route('admin.reservations.index')->with('success', '予約を削除しました。');
    }
}
