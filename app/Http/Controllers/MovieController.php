<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Movie;

class MovieController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin()
    {
        $movies = Movie::all();
        return view('admin', ['movies' => $movies]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('create');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $movie = Movie::findOrFail($id);
        return view('edit', ['movie' => $movie]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies,title,' . $request->id,
            'image_url' => 'required|url',
            'published_year' => 'required|integer|between:2000,2025',
            'description' => 'required|string',
            'is_showing' => 'boolean',
        ]);

        Movie::create([
            'title' => $validated['title'],
            'image_url' => $validated['image_url'],
            'published_year' => $validated['published_year'],
            'description' => $validated['description'],
            'is_showing' => $request->has('is_showing'),
        ]);

        return redirect()->back()->with('status', '映画が登録されました！');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:movies,title,' . $request->id,
            'image_url' => 'required|url',
            'published_year' => 'required|integer|between:2000,2025',
            'description' => 'required|string',
            'is_showing' => 'boolean',
        ]);

        $movie = Movie::findOrFail($id);
        $movie->update([
            'title' => $validated['title'],
            'image_url' => $validated['image_url'],
            'published_year' => $validated['published_year'],
            'description' => $validated['description'],
            'is_showing' => $request->has('is_showing'),
        ]);

        return redirect('/admin/movies/' . $id . '/edit')->with('status', '映画が更新されました！');
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return redirect('/admin/movies')->with('success', '映画が削除されました');
    }
}