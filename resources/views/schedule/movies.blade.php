<!DOCTYPE html>
<html>
<head>
    <title>映画スケジュール一覧</title>
</head>
<body>

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    @foreach ($movies as $movie)
        <h2>{{ $movie->id }}: {{ $movie->title }}</h2>
        <ul>
            @foreach ($movie->schedules as $schedule)
                <li>
                    開始時間: 
                    <a href="{{ url('/admin/schedules/' . $schedule->id) }}">
                        {{ $schedule->start_time->format('Y-m-d H:i') }} - {{ $schedule->end_time->format('H:i') }}
                    </a>
                    <a href="{{ url('/admin/schedules/' . $schedule->id . '/edit') }}">編集</a>
                    <form action="{{ url('/admin/schedules/' . $schedule->id . '/destroy') }}" method="post" style="display:inline;">
                        @csrf
                        @method('delete')
                        <button type="submit">削除</button>
                    </form>
                </li>
            @endforeach
        </ul>
        <a href="{{ url('/admin/movies/' . $movie->id . '/schedules/create') }}">スケジュールを追加</a>
    @endforeach

    @foreach ($moviesWithoutSchedules as $movie)
        @if ($movie->schedules->isEmpty())
            <h2>{{ $movie->id }}: {{ $movie->title }}</h2>
            @if ($movie->is_showing == 0)
                <p>※この映画は現在上映予定です。スケジュールを登録すると、上映中へ変更されます。</p>
            @endif
            <a href="{{ route('admin.movies.schedules.create', ['id' => $movie->id]) }}">スケジュールを追加</a>
        @endif
    @endforeach

    <h2>スケジュール自動生成のフォーム</h2>
    <form id="autoScheduleForm" method="get">
        <label for="date">スケジュール自動生成日付:</label>
        <select name="date" id="date">
            @for ($i = 0; $i < 8; $i++)
                <option value="{{ \Carbon\Carbon::now()->addDays($i)->format('Y-m-d') }}">
                    {{ \Carbon\Carbon::now()->addDays($i)->format('Y-m-d') }}
                </option>
            @endfor
        </select>
        <button type="button" onclick="submitForm()">スケジュール自動生成</button>
    </form>

    <script>
        function submitForm() {
            var date = document.getElementById('date').value;
            var url = "{{ url('/admin/schedules/create') }}" + '/' + date;
            window.location.href = url;
        }
    </script>

    <div>
        <a href="{{ route('admin.movies.index') }}">映画一覧</a>
    </div>
</body>
</html>
