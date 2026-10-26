<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    /**
     * Show the login page.
     */
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard.main');
        }

        return view('pages.auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(LoginRequest $request)
    {
        $loginInput = $request->input('username');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // Determine if input is formatted as an email
        $isEmail = filter_var($loginInput, FILTER_VALIDATE_EMAIL);

        // Try primary authentication credential
        $attempts = [];

        if ($isEmail) {
            $attempts[] = ['email' => $loginInput, 'password' => $password];
        } else {
            // First check by username column
            $attempts[] = ['username' => $loginInput, 'password' => $password];
            // Then fallback to email or name
            $attempts[] = ['email' => $loginInput, 'password' => $password];
            $attempts[] = ['name' => $loginInput, 'password' => $password];
        }

        foreach ($attempts as $attempt) {
            if (Auth::attempt($attempt, $remember)) {
                $request->session()->regenerate();

                return redirect()->intended(route('dashboard.main'));
            }
        }

        return back()
            ->withErrors([
                'username' => 'Nama pengguna atau password yang Anda masukkan salah.',
            ])
            ->onlyInput('username');
    }

    /**
     * Destroy an authenticated session.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
