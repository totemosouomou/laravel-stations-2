<!DOCTYPE html>
<html>
<head>
    <title>スケジュール詳細</title>
</head>
<body>
    <h2>スケジュール詳細</h2>
    <p>映画ID: {{ $schedule->movie_id }}</p>
    <p>映画タイトル: <a href="{{ url('/admin/movies/' . $schedule->movie_id) }}">{{ $schedule->movie->title }}</a></p>
    <p>開始時刻: {{ $schedule->start_time->format('H:i') }}</p>
    <p>終了時刻: {{ $schedule->end_time->format('H:i') }}</p>
    <p>作成日時: {{ $schedule->created_at }}</p>
    <p>更新日時: {{ $schedule->updated_at }}</p>
</body>
</html>
