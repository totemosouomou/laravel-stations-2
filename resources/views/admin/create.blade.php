<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>映画登録</title>
</head>
<body>
    <h1>映画登録</h1>

    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('status'))
        <div>
            {{ session('status') }}
        </div>
    @endif

    @if (session('success'))
        <div>{{ session('success') }}</div>
    @endif

    <form action="{{ url('/admin/movies/store') }}" method="post">
        @csrf
        <div>
            <label for="title">映画タイトル:</label>
            <input type="text" id="title" name="title" value="{{ old('title') }}">
        </div>
        <div>
            <label for="image_url">画像URL:</label>
            <input type="text" id="image_url" name="image_url" value="{{ old('image_url') }}">
        </div>
        <div>
            <label for="published_year">公開年:</label>
            <select id="published_year" name="published_year">
                @for ($year = 2000; $year <= 2024; $year++)
                    <option value="{{ $year }}" {{ old('published_year', 2024) == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
        </div>
        <div>
            <label for="description">概要:</label>
            <textarea id="description" name="description">{{ old('description') }}</textarea>
        </div>
        <div>
            <label for="is_showing">上映中かどうか:</label>
            <input type="checkbox" id="is_showing" name="is_showing" value="1" {{ old('is_showing') ? 'checked' : '' }}>
        </div>
        <div>
            <label for="genre">ジャンル:</label>
            <input type="text" id="genre" name="genre" value="{{ old('genre') }}">
        </div>
        <div>
            <button type="submit">登録</button>
        </div>
    </form>
</body>
</html>
