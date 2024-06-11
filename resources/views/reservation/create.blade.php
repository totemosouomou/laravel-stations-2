<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席予約フォーム</title>
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
        .selected-seat {
            background-color: blue;
            color: white;
        }
        .selected-seat a {
            color: white;
        }
    </style>
</head>
<body>
    <h1>予約追加フォーム</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div>
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.reservations.store') }}" method="post">
        @csrf

        @if($date && $movie_id)
            <input type="text" name="date" value="{{ $date ?? '' }}">
            <input type="text" name="movie_id" value="{{ $movie_id ?? '' }}">
            <input type="text" name="sheet_id" value="{{ $sheet_id ?? '' }}">
        @endif

        <div>
            <label for="schedule_id">日時</label>
            <select name="schedule_id" id="schedule_id" onchange="updateDate()">
                <option value="">選択してください</option>
                @foreach($schedules as $schedule => $dateSchedules)
                    <optgroup label="{{ $schedule }}">
                    @foreach($dateSchedules as $schedule)
                        @php
                            $isReserved = false;
                            $startTime = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
                            $endTime = \Carbon\Carbon::parse($schedule->end_time)->format('H:i');
                        @endphp
                        @foreach($schedule->reservations as $reservation)
                            @if($reservation->sheet_id == $sheet_id)
                                @php
                                    $isReserved = true;
                                    break;
                                @endphp
                            @endif
                        @endforeach
                        @if($isReserved)
                            @continue
                        @endif
                        <option value="{{ $schedule->id }}" data-start="{{ $schedule->start_time }}" {{ $schedule_id == $schedule->id ? 'selected' : '' }}>
                            {{ $startTime }} - {{ $endTime }}：{{ $schedule->movie->title }}
                        </option>
                    @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <table id="seat-table">
            <thead>
                <tr>
                    <th colspan="5">スクリーン</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 0; $i < 3; $i++)
                    <tr>
                        @for ($j = 0; $j < 5; $j++)
                            @php
                                $seatIndex = $i * 5 + $j;
                                $seat = $sheets->get($seatIndex);
                                $isReserved = $reservations ? $reservations->where('sheet_id', $seat->id ?? 0)->isNotEmpty() : false;
                                $isSelected = $seat && $seat->id == $sheet_id;
                            @endphp
                            <td class="{{ $isReserved ? 'reserved' : '' }} {{ $isSelected ? 'selected-seat' : '' }}">
                                @if ($seat && (!$isReserved || !$date || !$movie_id))
                                    @if($date && $movie_id)
                                        <a href="{{ route('admin.reservations.create') }}?schedule_id={{ $schedule_id }}&date={{ $date }}&sheet_id={{ $seat->id }}"
                                            onclick="event.preventDefault(); setSheetId({{ $seat->id }});">
                                            {{ $seatIndex + 1 }}
                                        </a>
                                    @else
                                        <a href="{{ route('admin.reservations.create') }}?sheet_id={{ $seat->id }}">
                                            {{ $seatIndex + 1 }}
                                        </a>
                                    @endif
                                @else
                                    {{ $seat ? $seatIndex + 1 : '&nbsp;' }}
                                @endif
                            </td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
        <div>
            <label for="name">お名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}">
        </div>
        <div>
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
        </div>
        <button type="submit">予約追加</button>
    </form>

<script>
function updateDate() {
    var selectedScheduleId = document.getElementById("schedule_id").value;
    if (selectedScheduleId) {
        var selectedStartTime = document.querySelector('option[value="' + selectedScheduleId + '"]').getAttribute('data-start');
        var selectedDate = selectedStartTime.substring(0, 10); // YYYY-MM-DDの形式の日付を取得
        var url = '/admin/reservations/create?schedule_id=' + selectedScheduleId + '&date=' + selectedDate;
        @if(isset($sheet_id))
            url += '&sheet_id={{ $sheet_id }}';
        @endif
        window.location.href = url;
    } else {
        // 選択が解除された場合、日付を空にする
        document.getElementById("date").value = "";
    }
}
function setSheetId(sheetId) {
    // 既に選択されたシートの色を元に戻す
    const previouslySelected = document.querySelector('td.selected-seat');
    if (previouslySelected) {
        previouslySelected.classList.remove('selected-seat');
    }

    // 現在選択されたシートの色を青色に変える
    const selectedLink = document.querySelector(`a[href*="sheet_id=${sheetId}"]`);
    if (selectedLink) {
        selectedLink.parentElement.classList.add('selected-seat');
        document.querySelector('input[name="sheet_id"]').value = sheetId;
    }
}
</script>
</body>
</html>
