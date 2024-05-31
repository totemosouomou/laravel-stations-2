<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>映画編集</title>
    <style>
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-weight: bold;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group .error {
            color: red;
            font-size: 0.9em;
        }
        .form-group .success {
            color: green;
            font-size: 0.9em;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>映画編集</h1>

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

    <form action="{{ url('/admin/movies/' . $movie->id . '/update') }}" method="post">
        @csrf
        @method('patch')

        <!-- タイトル -->
        <div class="form-group">
            <label for="title">タイトル:</label>
            <input type="text" id="title" name="title" value="{{ $movie->title }}">
            @error('title')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- 画像URL -->
        <div class="form-group">
            <label for="image_url">画像URL:</label>
            <input type="text" id="image_url" name="image_url" value="{{ $movie->image_url }}">
            @error('image_url')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- 公開年 -->
        <div class="form-group">
            <label for="published_year">公開年:</label>
            <select id="published_year" name="published_year">
                @for ($year = 2000; $year <= 2024; $year++)
                    <option value="{{ $year }}" {{ $movie->published_year == $year ? 'selected' : '' }}>
                        {{ $year }}
                    </option>
                @endfor
            </select>
            @error('published_year')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- ジャンル -->
        <div class="form-group">
            <label for="genre">ジャンル:</label>
            <input type="text" id="genre" name="genre" value="{{ $movie->genre ? $movie->genre->name : '' }}" required>
            @error('genre')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- 上映中かどうか -->
        <div class="form-group">
            <label for="is_showing">上映中かどうか:</label><br>
            <input type="radio" id="is_showing" name="is_showing" value="1" {{ $movie->is_showing == 1 ? 'checked' : '' }}>
            <label for="is_showing">はい</label><br>
            <input type="radio" id="is_not_showing" name="is_showing" value="0" {{ $movie->is_showing == 0 ? 'checked' : '' }}>
            <label for="is_not_showing">いいえ</label><br>
            @error('is_showing')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- 説明 -->
        <div class="form-group">
            <label for="description">説明:</label><br>
            <textarea id="description" name="description">{{ $movie->description }}</textarea>
            @error('description')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- 送信ボタン -->
        <button type="submit">送信</button>
    </form>
</body>
</html>
