<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SSOController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

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


Route::post('/sso-login-1', [SSOController::class, 'ssoLogin']);
// Route::get('/sso-login', [SSOController::class, 'handleTokenLogin']);
Route::post('/sso-login-2', [SSOController::class, 'handleTokenLogin']);

Route::get('/sso-login', function (Request $request) {
    if (! $request->hasValidSignature()) {
        abort(401);
    }

    $user = \App\Models\User::findOrFail($request->user);
    Auth::login($user);

    return redirect($request->redirect_to ?? route('dashboard'));
})->name('foodpanda.sso.login');


Route::get('/logtest', [AuthController::class, 'logtest']);


