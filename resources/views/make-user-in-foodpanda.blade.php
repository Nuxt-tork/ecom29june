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
        {{-- @dd($token) --}}
        {{-- <iframe src="http://127.0.0.1:1001/sso-login?token={{ urlencode($token) }}" style="display:none;"></iframe> --}}

        <form method="POST" action="{{ url('/make-user-in-foodpanda') }}">
            @csrf
            <label>Email:</label><br>
            <input type="email" name="email" required><br><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit">Make User</button>
        </form>
        @if(session('message'))
            <p>{{ session('message') }}</p>
        @endif
    </body>
</html>
