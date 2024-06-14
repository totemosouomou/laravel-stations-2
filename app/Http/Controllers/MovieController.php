<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Http\Requests\CreateMovieRequest;
use App\Http\Requests\UpdateMovieRequest;
use App\Models\Movie;
use App\Models\Genre;

class MovieController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function admin()
    {
        $movies = Movie::with('schedules')->get();

        return view('admin.movie', ['movies' => $movies]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function detail($id)
    {
        // 指定されたIDの映画を取得
        $movie = Movie::with('schedules')->findOrFail($id);
        // $movie = Movie::with('genre', 'schedules')->findOrFail($id);

        // ジャンル情報がない場合は新しい Genre インスタンスを設定
        // if (!$movie->genre) {
        //     $movie->genre = new Genre();
        // }

        // スケジュール情報がない場合は新しい Schedule インスタンスを設定
        if (!$movie->schedules) {
            $movie->schedules = new Schedule();
        }

        return view('admin.detail', ['movie' => $movie]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.create');
    }

    public function edit($id)
    {
        // ジャンル情報を含めて映画を取得
        $movie = Movie::with('genre')->findOrFail($id);

        // ジャンル情報がない場合は新しい Genre インスタンスを設定
        if (!$movie->genre) {
            $movie->genre = new Genre();
        }

        return view('admin.edit', ['movie' => $movie]);
    }

    public function store(CreateMovieRequest $request)
    {
        // バリデーション
        $validator = Validator::make($request->all(), [
            'title' => 'max:255',
            'genre' => 'max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 500);
        }

        // 修正 DB::transaction(function () use ($request, $validated) {
        DB::beginTransaction();

        try {
            // ジャンルを取得または作成
            // 修正 $genre = Genre::firstOrCreate(['name' => $request->input('genre')]);
            $genreName = $request->input('genre');
            $genre = Genre::firstOrCreate(['name' => $genreName]);

            // 映画データを作成
            $movieData = [
                'title' => $request->title,
                'image_url' => $request->image_url,
                'published_year' => $request->published_year,
                'description' => $request->description,
                'is_showing' => $request->has('is_showing'),
                'genre_id' => $genre->id,
            ];

            // 映画を作成
            $movie = Movie::create($movieData);

            DB::commit();

            return redirect('/admin/movies')->with('success', '映画が登録されました！');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['error' => $e->validator->errors()->all()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('エラーが発生しました: ' . $e->getMessage());
            return response()->json(['error' => '映画の登録中にエラーが発生しました'], 500);
        }
    }

    public function update(UpdateMovieRequest $request, $id)
    {
        // ジャンル情報を含めて映画を取得
        $movie = Movie::with('genre')->findOrFail($id);

        // ジャンル情報がない場合は新しい Genre インスタンスを設定
        if (!$movie->genre) {
            $movie->genre = new Genre();
        }

        // バリデーション
        $validator = Validator::make($request->all(), [
            'title' => 'max:255',
            'genre' => 'max:255',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 500);
        }

        DB::beginTransaction();

        try {
            // ジャンルの処理
            $genreName = $request->input('genre');
            $genre = Genre::firstOrCreate(['name' => $genreName]);

            // 映画データを作成
            $movieData = [
                'title' => $request->title,
                'image_url' => $request->image_url,
                'published_year' => $request->published_year,
                'description' => $request->description,
                'is_showing' => $request->has('is_showing'),
                'genre_id' => $genre->id,
            ];

            // 映画を更新
            $movie = Movie::findOrFail($id);
            $movie->update($movieData);

            DB::commit();

            return redirect()->route('admin.movies.edit', $movie->id)->with('success', '映画を更新しました');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json(['error' => $e->validator->errors()->all()], 422);
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error('エラーが発生しました: ' . $e->getMessage());
            return response()->json(['error' => '映画の登録中にエラーが発生しました'], 500);
        }
    }

    public function destroy($id)
    {
        $movie = Movie::findOrFail($id);
        $movie->delete();

        return redirect('/admin/movies')->with('success', '映画が削除されました');
    }
}
