<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ecommerce</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
</head>
<body class="antialiased">
    @php
        Log::info('working');
    @endphp
    <form method="POST" action="/login">
        @csrf
        <h2>Login</h2>
        <input type="email" id="email" name="email" placeholder="Email" required><br>
        <input type="password" id="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <a href="{{ route('register') }}">Don't have an account? Register</a>

    <script>
        document.getElementById('email').value = 'r@mail.com';
        document.getElementById('password').value = '112233';
    </script>
</body>
</html>