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
                            $seatIndex = $i * 5 + $j;
                            $seat = $sheets->get($seatIndex - 1);
                            $isReserved = $reservations->where('sheet_id', $seat->id ?? 0)->isNotEmpty();
                        @endphp
                        <td class="{{ $isReserved ? 'reserved' : '' }}">
                            @if ($seat && !$isReserved)
                                <a href="{{ route('user.reservations.create', ['id' => $schedule->movie->id, 'scheduleId' => $schedule->id]) }}?sheetId={{ $seat->id }}&date={{ request()->query('date') }}">
                                    {{ $seatIndex }}
                                </a>
                            @else
                                {{ $seat ? $seatIndex : '&nbsp;' }}
                            @endif
                        </td>
                    @endfor
                </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>
