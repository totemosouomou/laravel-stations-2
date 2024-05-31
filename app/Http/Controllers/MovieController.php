<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Movie;
use App\Models\Genre;

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
        return view('admin.movie', ['movies' => $movies]);
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

    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:movies',
            'image_url' => 'required|url',
            'published_year' => 'required|integer|between:2000,2024',
            'description' => 'required|string',
            'is_showing' => 'required|boolean',
            'genre' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 302);
        }

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

    public function update(Request $request, $id)
    {
        // ジャンル情報を含めて映画を取得
        $movie = Movie::with('genre')->findOrFail($id);

        // ジャンル情報がない場合は新しい Genre インスタンスを設定
        if (!$movie->genre) {
            $movie->genre = new Genre();
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|unique:movies,title,' . $id,
            'image_url' => 'required|url',
            'published_year' => 'required|integer|between:2000,2024',
            'description' => 'required|string',
            'is_showing' => 'required|boolean',
            'genre' => 'required|string',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json(['errors' => $errors], 302);
        }

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

// public function update(Request $request, $id)
// {
//     // ジャンル情報を含めて映画を取得
//     $movie = Movie::with('genre')->findOrFail($id);

//     // ジャンル情報がない場合は新しい Genre インスタンスを設定
//     if (!$movie->genre) {
//         $movie->genre = new Genre();
//     }

//     $validators = [];

//     // 変更がない場合はバリデーションをスキップするためのチェック
//     $isTitleChanged = $movie->title !== $request->input('title');
//     $isImageUrlChanged = $movie->image_url !== $request->input('image_url');
//     $isPublishedYearChanged = $movie->published_year !== $request->input('published_year');
//     $isDescriptionChanged = $movie->description !== $request->input('description');
//     $isShowingChanged = $movie->is_showing !== $request->input('is_showing');
//     $isGenreChanged = $movie->genre->name !== $request->input('genre');

//     if ($isTitleChanged) {
//         $validators[] = Validator::make($request->only('title'), [
//             'title' => 'required|string|unique:movies,title,' . $id,
//         ]);
//     }
//     if ($isImageUrlChanged) {
//         $validators[] = Validator::make($request->only('image_url'), [
//             'image_url' => 'required|url',
//         ]);
//     }
//     if ($isPublishedYearChanged) {
//         $validators[] = Validator::make($request->only('published_year'), [
//             'published_year' => 'required|integer|between:2000,2024',
//         ]);
//     }
//     if ($isDescriptionChanged) {
//         $validators[] = Validator::make($request->only('description'), [
//             'description' => 'required|string',
//         ]);
//     }
//     if ($isShowingChanged) {
//         $validators[] = Validator::make($request->only('is_showing'), [
//             'is_showing' => 'required|boolean',
//         ]);
//     }
//     if ($isGenreChanged) {
//         $validators[] = Validator::make($request->only('genre'), [
//             'genre' => 'required|string',
//         ]);
//     }

//     $errors = collect();
//     foreach ($validators as $validator) {
//         if ($validator->fails()) {
//             $errors = $errors->merge($validator->errors()->all());
//         }
//     }

//     if ($errors->isNotEmpty()) {
//         return response()->json(['errors' => $errors], 302);
//     }

//     // 汎用的なバリデーション
//     $validator = Validator::make($request->all(), [
//         'title' => 'max:255',
//         'genre' => 'max:255',
//     ]);

//     if ($validator->fails()) {
//         return response()->json(['errors' => $validator->errors()->all()], 500);
//     }

//     DB::beginTransaction();

//     try {
//         // ジャンルの処理
//         $genreName = $request->input('genre');
//         $genre = Genre::firstOrCreate(['name' => $genreName]);

//         // 映画データを作成
//         $movieData = [
//             'title' => $request->title,
//             'image_url' => $request->image_url,
//             'published_year' => $request->published_year,
//             'description' => $request->description,
//             'is_showing' => $request->has('is_showing'),
//             'genre_id' => $genre->id,
//         ];

//         // 映画を更新
//         $movie = Movie::findOrFail($id);
//         $movie->update($movieData);

//         DB::commit();

//         return redirect()->route('admin.movies.edit', $movie->id)->with('success', '映画を更新しました');

//     } catch (\Illuminate\Validation\ValidationException $e) {
//         DB::rollback();
//         return response()->json(['error' => $e->validator->errors()->all()], 422);
//     } catch (\Exception $e) {
//         DB::rollback();
//         \Log::error('エラーが発生しました: ' . $e->getMessage());
//         return response()->json(['error' => '映画の登録中にエラーが発生しました'], 500);
//     }
// }