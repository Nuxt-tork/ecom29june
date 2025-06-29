In Ecommerce App:

Web.php (Routes)

Route::middleware('guest')->group(function () {

    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::middleware('auth')->group(function () {

    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/my-product', [AuthController::class, 'myProduct'])->name('my-product');
});

Controller:

public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            
            // Create a secure token (can also use Laravel signed URLs)

            return redirect('/my-product');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

my-product view:

body class="antialiased">

        @php

            $user = auth()->user();
            $token = null;

            if ($user != ''){
                $token = encrypt([
                    'email' => $user->email,
                    'name'  => $user->name,
                    'timestamp' => now()->timestamp,
                ]);
                
            }
            
            @endphp
            
            <iframe src="http://127.0.0.1:1001/sso-login?token={{ urlencode($token) }}" ></iframe>


in DeliveryApp:

Route::get('/sso-login', [SSOController::class, 'handleTokenLogin']);

 public function handleTokenLogin(Request $request)
    {
        try {
            $data = decrypt($request->token);
        } catch (\Exception $e) {
            abort(403, 'Invalid token');
        }

        // Optional: add timestamp check to limit token expiry
        if (now()->timestamp - $data['timestamp'] > 60) {
            abort(403, 'Token expired');
        }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('112233'),
            ]);
        }

        Auth::login($user);

        return response()->noContent();
    }

This how its working fine now, I have done the token calculation in the blade




