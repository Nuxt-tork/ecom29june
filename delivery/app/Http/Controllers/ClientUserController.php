<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Validator;

class ClientUserController extends Controller
{
    //

     public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_secret' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $clientSecret = $request->client_secret;

        // Loop through each client and try to decrypt
        $client = Client::all()->first(function ($client) use ($clientSecret) {
            try {
                return Crypt::decryptString($client->secret) === $clientSecret;
            } catch (\Exception $e) {
                return false;
            }
        });

        if (!$client) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid client secret.',
            ], 401);
        }

        $user = User::create([
            'name' => 'ClientUser-' . now()->timestamp,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully.',
            'user_id' => $user->id,
        ], 201);
    }

    public function registerUser(Request $request)
    {
        $clientSecret = $request->input('client_secret');

        $client = \App\Models\Client::where('secret', $clientSecret)->first();

        if (! $client) {
            Log::warning('Unauthorized client attempt', ['provided_secret' => $clientSecret]);
            return response()->json(['message' => 'Unauthorized client'], 401);
        }

        $user = User::firstOrCreate(
            ['name' => $request->email],
            ['email' => $request->email],
            ['password' => Hash::make($request->password)]
        );

        Log::info('User registered or retrieved for client', [
            'client' => $client->client_name,
            'email' => $user->email,
        ]);

        $signedUrl = URL::temporarySignedRoute(
            'foodpanda.sso.login',
            now()->addMinutes(2),
            [
                'user' => $user->id,
                'redirect_to' => $request->input('redirect_to')
            ]
        );

        Log::info('Generated signed login URL', [
            'email' => $user->email,
            'login_url' => $signedUrl
        ]);

        return response()->json([
            'message' => 'User created',
            'login_url' => $signedUrl,
        ]);
    }

    // By form user create only
    //  public function registerUser(Request $request)
    // {
    //     // 1. Validate input
    //     $validator = Validator::make($request->all(), [
    //         'client_secret' => 'required|string',
    //         'email' => 'required|email',
    //         'password' => 'required|string',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'message' => 'Invalid input',
    //             'errors' => $validator->errors(),
    //         ], 422);
    //     }

    //     // 2. Authenticate the client
    //     $client = Client::where('secret', $request->client_secret)->first();

    //     if (! $client) {
    //         return response()->json([
    //             'message' => 'Unauthorized client',
    //         ], 401);
    //     }

    //     // 3. Create user if not exists
    //     $existingUser = User::where('email', $request->email)->first();

    //     if ($existingUser) {
    //         return response()->json([
    //             'message' => 'User already exists on provider',
    //         ], 200); // Not an error
    //     }

    //     // 4. Create new user
    //     $user = User::create([
    //         'name' => $request->email, // or assign default/generic
    //         'email' => $request->email,
    //         'password' => $request->password, // Already hashed on Ecommerce side
    //         'client_id' => $client->id, // if you track this relation
    //     ]);

        

    //     return response()->json([
    //         'message' => 'User created successfully in provider app.',
    //         'user_id' => $user->id,
    //     ], 201);
    // }
}
