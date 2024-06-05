<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席の空き状況</title>
    <style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
        a {
            text-decoration: none;
            color: blue;
        }
    </style>
</head>
<body>
    <h1>{{ $schedule->movie->title }} - 座席配置</h1>

    @if (session('error'))
        <div>
            {{ session('error') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th colspan="5">スクリーン</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 3; $i++)
            <tr>
                @for ($j = 1; $j <= 5; $j++)
                    @php
                        $seat = $sheets->where('row', chr(ord('a') + $i))->where('column', $j)->first();
                    @endphp
                    <td>
                        @if ($seat)
                            <a href="{{ route('user.reservations.create', ['id' => $schedule->movie->id, 'scheduleId' => $schedule->id]) }}?sheetId={{ $seat->id }}&date={{ $schedule->start_time->format('Y-m-d') }}">
                                {{ $seat->row . '-' . $seat->column }}
                            </a>
                        @else
                            &nbsp;
                        @endif
                    </td>
                @endfor
            </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>
