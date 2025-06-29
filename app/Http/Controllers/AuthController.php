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
   public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            
            // Create a secure token (can also use Laravel signed URLs)

            $user = auth()->user();
            $t = null;

            if ($user != ''){
                $t = encrypt([
                    'email' => $user->email,
                    'name'  => $user->name,
                    'timestamp' => now()->timestamp,
                ]);
                
            }
            
            try {
                    Http::post('http://127.0.0.1:1001/sso-login', [
                        'key' => $t,
                        'email' => $user->email,
                        'name' => $user->name,
                    ]);

                     Log::info('Called to Delivery with token:', ['token' => $t]);
                } catch (\Exception $e) {
                    Log::error('SSO delivery request failed: ' . $e->getMessage());
                }


           

        return response()->noContent();
            // return redirect('/my-product');
        }
        
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

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
}

