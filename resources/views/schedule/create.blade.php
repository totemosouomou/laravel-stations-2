<!DOCTYPE html>
<html>
<head>
    <title>スケジュールを追加</title>
</head>
<body>
    <h2>スケジュールを追加 - 映画ID: {{ $movie->id }}</h2>

    <form action="{{ url('/admin/movies/' . $movie->id . '/schedules/store') }}" method="post">
        @csrf
        <input type="hidden" name="movie_id" value="{{ $movie->id }}">
        <div>
            <label for="start_time_date">開始日付:</label>
            <input type="date" id="start_time_date" name="start_time_date" required>
        </div>
        <div>
            <label for="start_time_time">開始時間:</label>
            <input type="time" id="start_time_time" name="start_time_time" required>
        </div>
        <div>
            <label for="end_time_date">終了日付:</label>
            <input type="date" id="end_time_date" name="end_time_date" required>
        </div>
        <div>
            <label for="end_time_time">終了時間:</label>
            <input type="time" id="end_time_time" name="end_time_time" required>
        </div>
        <button type="submit">登録</button>
    </form>
</body>
</html>
