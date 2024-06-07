<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>座席予約フォーム</title>
</head>
<body>
    <h1>座席予約</h1>

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

    @if (session('status'))
        <div>
            {{ session('status') }}
        </div>
    @endif

    <form action="{{ route('user.reservations.store') }}" method="post">
        @csrf
        <input type="hidden" name="schedule_id" value="{{ $schedule_id }}">
        <input type="hidden" name="sheet_id" value="{{ request()->input('sheetId') }}">
        <input type="hidden" name="date" value="{{ request()->input('date') }}">

        <div>
            <label for="name">予約者氏名</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}">
        </div>

        <div>
            <label for="email">予約者メールアドレス</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}">
        </div>

        <button type="submit">予約する</button>
    </form>
</body>
</html>
