<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席の空き状況</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
        }
        a {
            text-decoration: none;
            color: blue;
        }
        .reserved {
            background-color: gray;
        }
    </style>
</head>
<body>
    <h1>予約一覧</h1>
    <a href="{{ route('admin.reservations.create') }}" class="btn btn-primary mb-3">予約追加</a>
    <table class="table">
        <thead>
            <tr>
                <th>映画作品</th>
                <th>座席</th>
                <th>日時</th>
                <th>名前</th>
                <th>メールアドレス</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reservations as $reservation)
                <tr>
                    <td>{{ $reservation->schedule->movie->title }}</td>
                    <td>{{ strtoupper($reservation->schedule->screen->name).$reservation->sheet->column }}</td>
                    <td>{{ $reservation->schedule->start_time }}</td>
                    <td>{{ $reservation->name }}</td>
                    <td>{{ $reservation->email }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div>
        <a href="{{ route('admin.movies.index') }}">映画一覧</a>
        <a href="{{ route('admin.movies.schedules.index') }}">スケジュール一覧</a>
    </div>
</body>
</html>
