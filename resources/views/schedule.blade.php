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
    <ul>
      @if (count($schedules) === 0)
          <span>本日の上映はありません。</span>
      @else
        @foreach($schedules as $schedule)
            <li>
                <span>{{ $schedule->movie->title }}</span>
                <span>開始時間: {{ $schedule->start_time->format('H:i') }}</span> -
                <span>{{ $schedule->end_time->format('H:i') }}</span>
            </li>
        @endforeach
      @endif
    </ul>
    <a href="{{ url('/movies') }}">映画一覧に戻る</a>
</body>
</html>
