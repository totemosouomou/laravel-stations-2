<!DOCTYPE html>
<html>
<head>
    <title>映画スケジュール一覧</title>
</head>
<body>
    @foreach ($movies as $movie)
        <h2>{{ $movie->id }}: {{ $movie->title }}</h2>
        <ul>
            @foreach ($movie->schedules as $schedule)
                <li>
                    開始時間: 
                    <a href="{{ url('/admin/schedules/' . $schedule->id) }}">
                        {{ $schedule->start_time->format('H:i') }} - {{ $schedule->end_time->format('H:i') }}
                    </a>
                    <a href="{{ url('/admin/schedules/' . $schedule->id . '/edit') }}">編集</a>
                    <form action="{{ url('/admin/schedules/' . $schedule->id . '/destroy') }}" method="post" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit">削除</button>
                    </form>
                </li>
            @endforeach
        </ul>
        <a href="{{ url('/admin/movies/' . $movie->id . '/schedules/create') }}">スケジュールを追加</a>
    @endforeach
</body>
</html>
