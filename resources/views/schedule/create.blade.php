<!DOCTYPE html>
<html>
<head>
    <title>スケジュールを追加</title>
</head>
<body>
    <h2>スケジュールを追加 - 映画ID: {{ $movie->id }}</h2>

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

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    @if ($movie->is_showing == 0)
        <p>※この映画は現在上映予定です。スケジュールを登録すると、上映中へ変更されます。</p>
    @endif

    <!-- 日付の選択 -->
    <form action="{{ route('admin.movies.schedules.create', ['id' => $movie->id]) }}" method="get">
        <label for="date">日付を指定してください:</label>
        <select id="date" name="date">
            @php
                $startDate = \Carbon\Carbon::parse($date);
                $endDate = $startDate->copy()->addDays(6);
            @endphp
            @for ($d = $startDate; $d <= $endDate; $d->addDay())
                <option value="{{ $d->format('Y-m-d') }}" {{ $d->eq(\Carbon\Carbon::parse($date)) ? 'selected' : '' }}>{{ $d->isoFormat('dddd, MMMM Do') }}</option>
            @endfor
        </select>
        <button type="submit">日付を指定</button>
    </form>

    <!-- 予約可能なスロット -->
    <h2>ボタンでスケジュールを設定する</h2>
    @foreach ($availableTimeSlots as $slot)
        @php
            $screen = $screens->firstWhere('id', $slot['screen_id']);
        @endphp
        <form action="{{ route('admin.movies.schedules.store', ['id' => $movie->id]) }}" method="post">
            @csrf
            <input type="hidden" name="movie_id" value="{{ $movie->id }}">
            <input type="hidden" name="screen_id" value="{{ $slot['screen_id'] }}">
            <input type="hidden" name="start_time_date" value="{{ $slot['start_time']->format('Y-m-d') }}">
            <input type="hidden" name="start_time_time" value="{{ $slot['start_time']->format('H:i') }}">
            <input type="hidden" name="end_time_date" value="{{ $slot['end_time']->format('Y-m-d') }}">
            <input type="hidden" name="end_time_time" value="{{ $slot['end_time']->format('H:i') }}">
            <button type="submit">
                {{ $slot['start_time']->format('H:i') }} - {{ $slot['end_time']->format('H:i') }}
                スクリーン：{{ $screen ? $screen->name : 'Unknown Screen' }}</button>
        </form>
    @endforeach

    <h2>フォームからスケジュールを設定する</h2>
    <form action="{{ route('admin.movies.schedules.store', ['id' => $movie->id]) }}" method="post">
        @csrf
        <input type="hidden" name="movie_id" value="{{ $movie->id }}">

        <div>
            <label for="screen_id">スクリーン:</label>
            <select id="screen_id" name="screen_id" required>
                @foreach($screens as $screen)
                    <option value="{{ $screen->id }}">{{ $screen->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="start_time_date">開始日付:</label>
            <input type="date" id="start_time_date" name="start_time_date" value="{{ request('date', $date) }}" required>
        </div>
        <div>
            <label for="start_time_time">開始時間:</label>
            <input type="time" id="start_time_time" name="start_time_time" value="" required>
        </div>
        <div>
            <label for="end_time_date">終了日付:</label>
            <input type="date" id="end_time_date" name="end_time_date" value="{{ request('date', $date) }}" required>
        </div>
        <div>
            <label for="end_time_time">終了時間:</label>
            <input type="time" id="end_time_time" name="end_time_time" value="" required>
        </div>
        <button type="submit">登録</button>
    </form>
</body>
</html>
