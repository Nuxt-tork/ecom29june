<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FoodpandaIntegrationController extends Controller
{
    public function form()
    {
        return view('make-user-in-foodpanda');
    }

    public function registerUser(Request $request)
    {
        $response = Http::post('http://127.0.0.1:1000/api/client/register-user', [
            'client_secret' => env('FOODPANDA_CLIENT_SECRET'),
            'email' => $request->email,
            'password' => $request->password,
        ]);

        if ($response->successful()) {
            return redirect()->back()->with('message', $response['message']);
        }

        return redirect()->back()->with('message', $response->json()['message'] ?? 'Something went wrong.');
    }
}
