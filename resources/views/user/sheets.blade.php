<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席表</title>
    <style>
        table {
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
        }
    </style>
</head>
<body>
    <h1>座席配置</h1>
    <table>
        <thead>
            <tr>
                <th colspan="6">スクリーン</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 3; $i++)
            <tr>
                @for ($j = 1; $j <= 5; $j++)
                    @php
                        $seat = $sheets->where('row', chr(ord('a') + $i))->where('column', $j)->first();
                    @endphp
                    <td>{{ $seat ? $seat->row . '-' . $seat->column : '' }}</td>
                @endfor
            </tr>
            @endfor
        </tbody>
    </table>
</body>
</html>
