<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SSOController extends Controller
{
    //
    public function ssoLogin(Request $request)
    {

        // dd($request->all());
        if ($request->header('X-SHARED-KEY') !== env('SSO_SHARED_KEY')) {
            abort(403);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Optionally auto-register or reject
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('12345') // dummy password
            ]);
        }

        Auth::login($user);

        return response()->json(['status' => 'logged_in']);
    }

    public function handleTokenLogin(Request $request)
    {

        Log::info('request=->all: ', $request->all());
        try {
            $data = decrypt($request->key);
        } catch (\Exception $e) {
            abort(403, 'Invalid token');
        }

        // Optional: add timestamp check to limit token expiry
        // if (now()->timestamp - $data['timestamp'] > 60) {
        //     abort(403, 'Token expired');
        // }

        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('112233'),
            ]);
        }

        Auth::login($user);

        return redirect('http://127.0.0.1:1000/');
    }



}
