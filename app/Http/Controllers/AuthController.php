<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required',
            'email'    => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Auth::login($user);

        return redirect('/my-product');
    }

    public function showLoginForm()
    {
        return view('login');
    }

    // Attept 1 with https
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (Auth::attempt($credentials)) {

    //         $user = auth()->user();

           
    //         Http::withHeaders([
    //             'X-SHARED-KEY' => env('SSO_SHARED_KEY'),
    //         ])->post('http://127.0.0.1:1001/sso-login', [
    //             'email' => $user->email,
    //             'name'  => $user->name,
    //         ]);

    //         return redirect('/my-product');
    //     }

    //     return back()->withErrors(['email' => 'Invalid credentials']);
    // }

    // Return and re redirect
//    public function login(Request $request)
//     {
//         $credentials = $request->only('email', 'password');

//         if (Auth::attempt($credentials)) {
            
//             // Create a secure token (can also use Laravel signed URLs)

//             $user = auth()->user();
//             $t = null;

//             if ($user != ''){
//                 $t = encrypt([
//                     'email' => $user->email,
//                     'name'  => $user->name,
//                     'timestamp' => now()->timestamp,
//                 ]);
                
//             }
            
//             try {
//                     Http::post('http://127.0.0.1:1001/sso-login', [
//                         'key' => $t,
//                         'email' => $user->email,
//                         'name' => $user->name,
//                     ]);

//                      Log::info('Called to Delivery with token:', ['token' => $t]);
//                 } catch (\Exception $e) {
//                     Log::error('SSO delivery request failed: ' . $e->getMessage());
//                 }


           

//         return response()->noContent();
//             // return redirect('/my-product');
//         }
        
//         return back()->withErrors(['email' => 'Invalid credentials']);
//     }

    // With iframe
    // public function login(Request $request)
    // {
    //     $credentials = $request->only('email', 'password');

    //     if (Auth::attempt($credentials)) {
    //         // Create a secure token (can also use Laravel signed URLs)
    //         $user = auth()->user();

    //         $token = encrypt([
    //             'email' => $user->email,
    //             'name'  => $user->name,
    //             'timestamp' => now()->timestamp,
    //         ]);

    //         // Redirect the browser to Delivery app with token
    //         $token = encrypt([
    //             'email' => $user->email,
    //             'name' => $user->name,
    //             'timestamp' => now()->timestamp,
    //         ]);

    //         return view('my-product', compact('token'));
    
    //     }
    // }

    public function logout()
    {
        Auth::logout();
        return redirect('/');
    }

    public function myProduct()
    {
        return view('my-product');
    }

    // Sign in and auto register in foodpanda
    // public function login(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'email' => ['required', 'email'],
    //         'password' => ['required'],
    //     ]);

    //     if (Auth::attempt($credentials)) {
    //         $request->session()->regenerate();

    //         // User login successful — now send to Foodpanda
    //         try {
    //             $response = Http::timeout(5)->post('http://127.0.0.1:1000/api/client/register-user', [
    //                 'client_secret' => env('FOODPANDA_CLIENT_SECRET'),
    //                 'email' => $request->email,
    //                 'password' => Hash::make($request->password), // hashed password
    //             ]);

    //             if ($response->successful()) {
    //                 logger()->info('Foodpanda user created Successfully', [
    //                     'status' => $response->status(),
    //                 ]);
    //                 return redirect()->intended(route('dashboard'))->with('message', 'Welcome back!');
    //             } else {
    //                 // log the error
    //                 logger()->warning('Foodpanda user creation failed', [
    //                     'status' => $response->status(),
    //                     'body' => $response->body(),
    //                 ]);
    //                 return redirect()->intended(route('dashboard'))->with('message', 'Logged in, but failed to sync with delivery system.');
    //             }

    //         } catch (\Exception $e) {
    //             logger()->error('Foodpanda API error: ' . $e->getMessage());
    //             return redirect()->intended(route('dashboard'))->with('message', 'Logged in, but delivery system unreachable.');
    //         }
    //     }

    //     return back()->withErrors([
    //         'email' => 'Invalid credentials.',
    //     ])->withInput();
    // }
 
    // Sign in and auto register in foodpanda with redirect Auth try
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            Log::info('User logged in on Ecommerce', ['email' => $request->email]);

            try {
                $response = Http::post('http://127.0.0.1:1000/api/client/register-user', [
                    'client_secret' => env('FOODPANDA_CLIENT_SECRET'),
                    'email' => $request->email,
                    'password' => $request->password,
                    'redirect_to' => route('dashboard'),
                ]);

                if ($response->successful() && $response->json('login_url')) {
                    Log::info('User sync request successful', [
                        'email' => $request->email,
                        'provider_response' => $response->json(),
                    ]);
                    return redirect()->away($response->json('login_url'));
                } else {
                    Log::warning('User sync failed or login_url missing', [
                        'email' => $request->email,
                        'response' => $response->body(),
                        'status' => $response->status(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Exception during user sync with Foodpanda', [
                    'email' => $request->email,
                    'exception' => $e->getMessage(),
                ]);
            }

            return redirect()->route('dashboard')->with('message', 'Logged in locally. Foodpanda login skipped.');
        }

        Log::warning('Failed login attempt', ['email' => $request->email]);

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput();
    }


}

