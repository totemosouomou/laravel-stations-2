<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movies</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        img {
            max-width: 100px;
            height: auto;
        }
        .alert {
            color: red;
        }
        .success {
            color: green;
        }
    </style>
</head>
<body>
    <h1>Movies</h1>

    @if ($errors->any())
        <div class="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div class="alert">
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>映画タイトル</th>
                <th>画像</th>
                <th>公開年</th>
                <th>上映中かどうか</th>
                <th>ジャンル</th>
                <th>概要</th>
                <th>最終上映日時</th>
                <th>登録日時</th>
                <th>更新日時</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movies as $movie)
                <tr>
                    <td>{{ $movie->id }}</td>
                    <td>{{ $movie->title }}</td>
                    <td><img src="{{ $movie->image_url }}" alt="{{ $movie->title }}"></td>
                    <td>{{ $movie->published_year }}</td>
                    <td>{{ $movie->is_showing ? '上映中' : '上映予定' }}</td>
                    <td>{{ $movie->genre ? $movie->genre->name : '未設定' }}</td>
                    <td>{{ $movie->description }}</td>
                    <td>
                        @if ($movie->schedules->isNotEmpty())
                            {{ $movie->schedules->sortByDesc('start_time')->first()->start_time->format('Y-m-d H:i') }}
                        @else
                            なし
                        @endif
                    </td>
                    <td>{{ $movie->created_at }}</td>
                    <td>{{ $movie->updated_at }}</td>
                    <td>
                        <a href="{{ url('/admin/movies/' . $movie->id . '/edit') }}">編集</a>
                        <form action="{{ url('/admin/movies/' . $movie->id . '/destroy') }}" method="post" style="display:inline;">
                            @csrf
                            @method('delete')
                            <button type="submit" onclick="return confirm('本当に削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div>
        <a href="{{ route('admin.movies.schedules.index') }}">スケジュール一覧</a>
        <a href="{{ route('admin.reservations.index') }}">予約一覧</a>
    </div>
</body>
</html>
