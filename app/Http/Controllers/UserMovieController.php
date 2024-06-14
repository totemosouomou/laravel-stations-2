<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Schedule;
use App\Models\Sheet;
use Carbon\Carbon;

class UserMovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // クエリパラメータを取得
        $isShowing = $request->query('is_showing');
        $keyword = $request->query('keyword');

        // クエリビルダーを初期化
        $query = Movie::query();

        // 公開中のみ
        if ($isShowing === '1') {
            $query->where('is_showing', 1);

        // 公開予定のみ
        } elseif ($isShowing === '0') {
            $query->where('is_showing', 0);
        }

        // キーワード検索
        if ($keyword) {
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        // クエリを実行して映画リストをページネーション
        $movies = $query->paginate(20);

        // 認証されているか確認
        if (!Auth::check()) {
            return view('user.index', ['movies' => $movies, 'reservations' => '']);
        }

        // 認証中ユーザーが持つ予約を表示
        $user = Auth::user();
        $reservations = $user->reservations()->with('schedule.movie')
            ->join('schedules', 'reservations.schedule_id', '=', 'schedules.id')
            ->where('schedules.end_time', '>', Carbon::now())
            ->orderBy('schedules.start_time')
            ->get();

        // ビューに渡す
        return view('user.index', ['movies' => $movies, 'reservations' => $reservations]);
    }

    public function schedules($id)
    {
        $movie = Movie::with('schedules')->findOrFail($id);
        $schedules = Schedule::with('movie')
            ->where('movie_id', $movie->id)
            ->where('end_time', '>', Carbon::now())
            ->orderBy('start_time', 'asc')
            ->get();

        return view('user.schedules', ['movie' => $movie, 'schedules' => $schedules]);
    }

    public function sheets()
    {
        $sheets = Sheet::all()->groupBy('screen_id');

        return view('user.sheets', ['sheets' => $sheets]);
    }
}