<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>編集</title>
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
<h1>予約編集</h1>

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

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="post" action="{{ route('admin.reservations.update', $myReservation->id) }}">
        @csrf
        @method('patch')

        <label for="movie_id">映画作品</label>
        <input type="text" name="movie_id" id="movie_id" value="{{ $myReservation->schedule->movie->id }}">

        <div>
            <label for="movie_id">日時</label>
            <select name="schedule_id" id="schedule_id">
                @foreach($schedules as $schedule => $dateSchedules)
                    <optgroup label="{{ $schedule }}">
                    @foreach($dateSchedules as $schedule)
                        @php
                            $startTime = \Carbon\Carbon::parse($schedule->start_time)->format('H:i');
                            $endTime = \Carbon\Carbon::parse($schedule->end_time)->format('H:i');
                        @endphp
                        <option value="{{ $schedule->id }}" data-movie-id="{{ $schedule->movie->id }}" {{ $myReservation->schedule_id == $schedule->id ? 'selected' : '' }}>
                            {{ $startTime }} - {{ $endTime }}：{{ $schedule->movie->title }}
                        </option>
                    @endforeach
                    </optgroup>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sheet_id">座席</label>
            <select name="sheet_id" id="sheet_id" class="form-control">
                @foreach($sheets as $sheet)
                    <option value="{{ $sheet->id }}" {{ $myReservation->sheet_id == $sheet->id ? 'selected' : '' }}>
                        {{ $sheet->row }}-{{ $sheet->column }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="name">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name', $myReservation->name) }}">
        </div>

        <div>
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email', $myReservation->email) }}">
        </div>

        <button type="submit">更新</button>
    </form>

    <form method="post" action="{{ route('admin.reservations.destroy', $myReservation->id) }}">
        @csrf
        @method('delete')
        <button type="submit">削除</button>
    </form>

<script>
function updateId() {

}
    document.addEventListener('DOMContentLoaded', function () {
        const scheduleSelect = document.getElementById('schedule_id');
        const movieIdInput = document.getElementById('movie_id');

        scheduleSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const movieId = selectedOption.getAttribute('data-movie-id');
            movieIdInput.value = movieId;
        });

        // Initialize the movie_id based on the current selection
        const selectedOption = scheduleSelect.options[scheduleSelect.selectedIndex];
        const movieId = selectedOption.getAttribute('data-movie-id');
        movieIdInput.value = movieId;
    });
</script>
</body>
</html>