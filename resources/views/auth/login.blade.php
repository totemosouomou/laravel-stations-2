<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>

    <div>
        @if(Route::currentRouteName() !== 'users.create')
            <a href="{{ route('users.create') }}">会員登録</a>
        @else
            会員登録
        @endif
        @if(Route::currentRouteName() !== 'login')
            <a href="{{ route('login') }}">ログイン</a>
        @else
            ログイン
        @endif
    </div>

    <form action="{{ route('login') }}" method="post">
        @csrf
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>
        <br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
        <input type="checkbox" onclick="togglePasswordVisibility('password')"> Show Password
        <br>
        <button type="submit">Login</button>
    </form>

    <script>
        function togglePasswordVisibility(id) {
            var x = document.getElementById(id);
            if (x.type === "password") {
                x.type = "text";
            } else {
                x.type = "password";
            }
        }
    </script>
</body>
</html>
