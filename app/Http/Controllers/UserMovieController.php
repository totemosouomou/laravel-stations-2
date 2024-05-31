<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;
use App\Models\Sheet;

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

        // ビューに渡す
        return view('index', ['movies' => $movies]);
    }

    public function sheets()
    {
        $sheets = Sheet::all();

        return view('sheets', ['sheets' => $sheets]);
    }
}