<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>映画館</title>
</head>
<body>
    <h1>映画館</h1>

    <!-- 検索フォーム -->
    <form action="{{ url('/movies') }}" method="get">
        <div>
            <input type="text" name="keyword" placeholder="キーワードで検索" value="{{ request()->query('keyword') }}">
        </div>
        <div>
            <input type="radio" id="all" name="is_showing" value="" {{ request()->query('is_showing') === null ? 'checked' : '' }}>
            <label for="all">すべて</label>
            <input type="radio" id="showing" name="is_showing" value="1" {{ request()->query('is_showing') === '1' ? 'checked' : '' }}>
            <label for="showing">公開中</label>
            <input type="radio" id="upcoming" name="is_showing" value="0" {{ request()->query('is_showing') === '0' ? 'checked' : '' }}>
            <label for="upcoming">公開予定</label>
        </div>
        <div>
            <button type="submit">検索</button>
        </div>
    </form>

    <!-- 映画リスト -->
    @foreach($movies as $movie)
        <div>
            <h2>{{ $movie->title }}</h2>
            <img src="{{ $movie->image_url }}" alt="{{ $movie->title }}">
            <a href="{{ url('/movies/' . $movie->id) }}">詳細・上映時間</a>
        </div>
    @endforeach

    <!-- ページネーションリンク -->
    <div>
        {{ $movies->links() }}
    </div>
</body>
</html>
