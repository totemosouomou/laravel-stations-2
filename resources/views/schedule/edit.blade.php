<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>編集</title>
</head>
<body>
    <h1>スケジュール更新</h1>

    <form method="post" action="{{ route('admin.schedules.update', ['scheduleId' => $schedule->id]) }}" >
        @foreach ($errors->all() as $error)
        {{$error}}<br>
        @endforeach
        @if (session('time_error'))
        {{ session('time_error') }}<br>
        @endif
        @csrf
        @method('PATCH')

        <input type="hidden" id="movie_id" name="movie_id" value="{{ $schedule->movie_id }}">

        <label for="start_time_date">開始日:</label>
        <input type="date" id="start_time_date" name="start_time_date" value="{{ $schedule->start_time->format('Y-m-d') }}">
        <label for="start_time_time">開始時間:</label>
        <input type="time" id="start_time_time" name="start_time_time" value="{{ $schedule->start_time->format('H:i') }}">

        <label for="end_time_date">終了日:</label>
        <input type="date" id="end_time_date" name="end_time_date" value="{{ $schedule->end_time->format('Y-m-d') }}">
        <label for="end_time_time">終了時間:</label>
        <input type="time" id="end_time_time" name="end_time_time" value="{{ $schedule->end_time->format('H:i') }}">

        <button type="submit">更新</button>
    </form>
</body>
</html>