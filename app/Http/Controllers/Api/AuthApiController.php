<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AuthApiController extends Controller
{
    /**
     * Public RESTful API Login
     * Mendukung autentikasi lokal dan autentikasi ke database domain remote.
     *
     * POST /api/login
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username atau email wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $loginInput = $request->input('username');
        $password = $request->input('password');
        $remember = $request->boolean('remember');

        // 1. Cek autentikasi di database lokal terlebih dahulu
        $user = User::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->orWhere('name', $loginInput)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            $token = $user->createToken('auth-token')->plainTextToken;

            // Login-kan session web
            Auth::login($user, $remember);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
                'source' => 'local',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role ?? 'Admin',
                ],
                'redirect_url' => route('dashboard.main'),
            ]);
        }

        // 2. Cek ke RESTful API domain remote (https://arkhsat.site/api/login.php)
        $remoteAuth = $this->checkRemoteLogin($loginInput, $password);
        if ($remoteAuth && !empty($remoteAuth['success']) && !empty($remoteAuth['user'])) {
            $remoteUser = $remoteAuth['user'];

            // Sinkronkan akun dari domain ke database lokal
            $user = User::updateOrCreate(
                ['username' => $remoteUser['username'] ?? $loginInput],
                [
                    'name' => $remoteUser['name'] ?? $loginInput,
                    'email' => $remoteUser['email'] ?? ($loginInput . '@domain.local'),
                    'password' => Hash::make($password),
                    'role' => $remoteUser['role'] ?? 'Admin',
                ]
            );

            $token = $user->createToken('auth-token')->plainTextToken;
            Auth::login($user, $remember);

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil via akun domain remote!',
                'source' => 'remote_domain',
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'email' => $user->email,
                    'role' => $user->role ?? 'Admin',
                ],
                'redirect_url' => route('dashboard.main'),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Username atau password yang Anda masukkan salah.',
        ], 401);
    }

    /**
     * Memeriksa kredensial login ke endpoint RESTful API domain remote
     */
    protected function checkRemoteLogin(string $username, string $password): ?array
    {
        try {
            $baseUrl = rtrim((string) config('services.monitoring_api.base_url'), '/');
            $token = (string) config('services.monitoring_api.token');

            // Coba di folder /api/login.php atau /login.php
            $endpoints = [
                str_ends_with($baseUrl, '.php') ? $baseUrl : "{$baseUrl}/login.php",
                preg_replace('/\/api$/', '', $baseUrl) . '/login.php',
            ];

            foreach (array_unique($endpoints) as $endpoint) {
                $response = Http::timeout(5)
                    ->withHeaders([
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json',
                        'Authorization' => "Bearer {$token}",
                        'X-API-TOKEN' => $token,
                    ])
                    ->post($endpoint, [
                        'username' => $username,
                        'password' => $password,
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }

                // Jika bukan 404, jangan fallback ke endpoint lain
                if ($response->status() !== 404) {
                    break;
                }
            }
        } catch (\Throwable $e) {
            Log::debug("Remote login check skipped or failed: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Dapatkan profil user yang sedang login via token
     *
     * GET /api/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role ?? 'Admin',
            ],
        ]);
    }

    /**
     * Logout via RESTful API
     *
     * POST /api/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $user->currentAccessToken()->delete();
        }

        if (Auth::check()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json([
            'success' => true,
            'message' => 'Anda telah berhasil logout.',
        ]);
    }
}
