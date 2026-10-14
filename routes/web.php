<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return view('auth.login');
})->name('login');

Route::get('/login', function () {
    return view('auth.login');
});

Route::post('/login', function (Request $request) {
    $request->validate([
        'username' => ['required'],
        'password' => ['required'],
    ]);

    // For now redirect directly to main dashboard
    return redirect()->route('dashboard.main');
})->name('login.post');

Route::get('/main', function () {
    return view('dashboard.main');
})->name('dashboard.main');


