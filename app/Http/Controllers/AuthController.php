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

        // 1. Coba autentikasi ke database lokal jika database aktif
        try {
            foreach ($attempts as $attempt) {
                if (Auth::attempt($attempt, $remember)) {
                    $user = Auth::user();
                    $userData = [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'email' => $user->email,
                        'role' => $user->role ?? 'Admin',
                    ];
                    session(['logged_user' => $userData]);
                    $request->session()->regenerate();
                    session()->save();

                    if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                        return response()->json([
                            'success' => true,
                            'message' => 'Login berhasil!',
                            'token' => 'local_' . bin2hex(random_bytes(24)),
                            'token_type' => 'Bearer',
                            'user' => $userData,
                            'redirect_url' => route('dashboard.main'),
                        ]);
                    }

                    return redirect()->intended(route('dashboard.main'));
                }
            }
        } catch (\Throwable $e) {
            // Database lokal tidak terhubung / tidak aktif
        }

        // 2. Fallback autentikasi ke RESTful API domain remote (arkhsat.site)
        try {
            $baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
            $token = (string) config('services.monitoring_api.token');
            $endpoints = [
                str_ends_with($baseUrl, '.php') ? $baseUrl : "{$baseUrl}/login.php",
                preg_replace('/\/api$/', '', $baseUrl) . '/login.php',
            ];

            foreach (array_unique($endpoints) as $endpoint) {
                $response = \Illuminate\Support\Facades\Http::timeout(5)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer {$token}",
                        'X-API-TOKEN' => $token,
                    ])
                    ->post($endpoint, [
                        'username' => $loginInput,
                        'password' => $password,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    if (!empty($json['success']) && !empty($json['user'])) {
                        $remoteUser = $json['user'];
                        $userData = [
                            'id' => (int) ($remoteUser['id'] ?? 1),
                            'name' => $remoteUser['name'] ?? $loginInput,
                            'username' => $remoteUser['username'] ?? $loginInput,
                            'email' => $remoteUser['email'] ?? ($loginInput . '@domain.local'),
                            'role' => $remoteUser['role'] ?? 'Admin',
                        ];

                        session(['logged_user' => $userData]);

                        $userModel = new User();
                        $userModel->forceFill($userData);
                        $userModel->exists = true;

                        Auth::login($userModel, $remember);
                        $request->session()->regenerate();
                        session()->save();

                        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
                            return response()->json([
                                'success' => true,
                                'message' => 'Login berhasil via akun domain remote!',
                                'token' => 'remote_' . bin2hex(random_bytes(24)),
                                'token_type' => 'Bearer',
                                'user' => $userData,
                                'redirect_url' => route('dashboard.main'),
                            ]);
                        }

                        return redirect()->intended(route('dashboard.main'));
                    }
                }
            }
        } catch (\Throwable $e) {
            // Remote API error
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => 'Nama pengguna atau password yang Anda masukkan salah.',
            ], 401);
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

        $request->session()->forget('logged_user');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
