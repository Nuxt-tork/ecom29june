<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

    public function logtest(Request $request)
    {
        Log::info('here');
     


    }

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

