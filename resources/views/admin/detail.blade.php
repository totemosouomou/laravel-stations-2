<!DOCTYPE html>
<html>
<head>
    <title>映画詳細</title>
</head>
<body>
    <h2>映画詳細 - {{ $movie->title }}</h2>
    <p>ID: {{ $movie->id }}</p>
    <p>タイトル: {{ $movie->title }}</p>
    <p><img src="{{ $movie->image_url }}" alt="{{ $movie->title }}"></p>
    <p>公開年: {{ $movie->published_year }}</p>
    <p>概要: {{ $movie->description }}</p>
    <p>上映中: {{ $movie->is_showing ? '上映中' : '上映予定' }}</p>
    <!-- <p>ジャンル: {{ $movie->genre ? $movie->genre->name : '未設定' }}</p> -->

    <h3>スケジュール</h3>
    <ul>
        @if ($movie->schedules === null || count($movie->schedules) === 0)
            <span>本日の上映はありません。</span>
        @else
            @foreach ($movie->schedules as $schedule)
                <li>
                    <span>{{ $movie->title }}</span>
                    <a href="{{ url('/admin/schedules/' . $schedule->id) }}">
                        <span>開始時間: {{ $schedule->start_time }}</span> -
                        <span>{{ $schedule->end_time }}</span>
                    </a>
                </li>
            @endforeach
        @endif
    </ul>

    <!-- 編集画面へのリンク -->
    <a href="{{ url('/admin/movies/' . $movie->id . '/edit') }}">編集画面へ</a>
</body>
</html>
