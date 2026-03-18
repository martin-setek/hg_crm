<?php

use App\Http\Controllers\AdvisorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', fn() => view('auth.login'))->name('login')->middleware('guest');
Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);
    if (Auth::attempt($credentials, $request->boolean('remember'))) {
        $request->session()->regenerate();
        return redirect()->intended('/');
    }
    return back()->withErrors(['email' => 'Nesprávné přihlašovací údaje.']);
})->middleware('guest');

Route::post('/logout', function (\Illuminate\Http\Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
})->name('logout');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Pipeline / Leads
    Route::resource('pipeline', LeadController::class);
    Route::patch('pipeline/{lead}/status', [LeadController::class, 'updateStatus'])
        ->name('pipeline.update-status');

    // Advisors
    Route::resource('advisors', AdvisorController::class)->only(['index', 'show', 'store', 'update']);
});
