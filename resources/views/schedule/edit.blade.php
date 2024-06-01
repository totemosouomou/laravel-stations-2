<!DOCTYPE html>
<html>
<head>
    <title>スケジュール編集</title>
</head>
<body>
    <h2>スケジュール編集 - 映画ID: {{ $schedule->movie->id }}</h2>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div>
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    <form action="{{ url('/admin/schedules/' . $schedule->id . '/update') }}" method="post">
        @csrf
        @method('patch')
        <input type="hidden" name="movie_id" value="{{ $schedule->movie->id }}">

        <div>
            <label for="start_time_date">開始日付:</label>
            <input type="date" id="start_time_date" name="start_time_date" value="{{ $schedule->start_time->format('Y-m-d') }}" required>
        </div>

        <div>
            <label for="start_time_time">開始時間:</label>
            <input type="time" id="start_time_time" name="start_time_time" value="{{ $schedule->start_time->format('H:i') }}" required>
        </div>

        <div>
            <label for="end_time_date">終了日付:</label>
            <input type="date" id="end_time_date" name="end_time_date" value="{{ $schedule->end_time->format('Y-m-d') }}" required>
        </div>

        <div>
            <label for="end_time_time">終了時間:</label>
            <input type="time" id="end_time_time" name="end_time_time" value="{{ $schedule->end_time->format('H:i') }}" required>
        </div>

        <button type="submit">更新</button>
    </form>
</body>
</html>
