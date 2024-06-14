<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $movie->title }}</title>
</head>
<body>
    <h1>{{ $movie->title }} (Movie_ID: {{ $movie->id }})</h1>
    <p>{{ $movie->published_year }}</p>
    <p>{{ $movie->description }}</p>
    <img src="{{ $movie->image_url }}" alt="{{ $movie->title }}">

    <h2>上映スケジュール</h2>

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    <ul>
        @if ($movie->is_showing == 0)
            <span>上映予定の作品です。スケジュール公開までお待ちください。</span>
        @elseif (count($schedules) === 0)
            <span>上映スケジュールはありません。</span>
        @else
            @foreach($schedules as $schedule)
                <li>
                    <span>{{ $schedule->movie->title }}</span>
                    <span>開始時間: {{ $schedule->start_time->format('Y-m-d H:i') }}</span> -
                    <span>{{ $schedule->end_time->format('H:i') }}</span>
                    <form action="/movies/{{ $movie->id }}/schedules/{{ $schedule->id }}/sheets" method="get">
                        <input type="hidden" name="date" value="{{ $schedule->start_time->format('Y-m-d') }}">
                        <button type="submit">座席を予約する</button>
                    </form>
                </li>
            @endforeach
        @endif
    </ul>
    <a href="{{ url('/movies') }}">映画一覧に戻る</a>
</body>
</html>
