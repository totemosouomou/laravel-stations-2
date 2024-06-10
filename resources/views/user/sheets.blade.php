<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席表</title>
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
    </style>
</head>
<body>
    <h1>座席配置</h1>
    @foreach ($sheets as $screenId => $screenSheets)
        <h2>スクリーン {{ $screenId }}</h2>
        <table>
            <thead>
                <tr>
                    <th colspan="5">スクリーン</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowCounter = 0;
                @endphp
                @foreach ($screenSheets as $sheet)
                    @if ($sheet->row !== chr(ord('a') + $rowCounter))
                        @php
                            $rowCounter++;
                        @endphp
                    @endif
                    @if ($loop->iteration % 5 == 1)
                        <tr>
                    @endif
                    <td>{{ $sheet->row . '-' . $sheet->column }}</td>
                    @if ($loop->iteration % 5 == 0 || $loop->last)
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    @endforeach
    <div>
        <a href="{{ route('user.movies.index') }}">映画一覧に戻る</a>
    </div>
</body>
</html>
