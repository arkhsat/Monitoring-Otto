<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;

class CustomUserProvider extends EloquentUserProvider
{
    /**
     * Retrieve a user by their unique identifier.
     * Mendukung database lokal, dengan fallback ke data sesi (jika tanpa database lokal).
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        // 1. Coba ambil dari database jika koneksi database aktif
        try {
            $user = parent::retrieveById($identifier);
            if ($user) {
                return $user;
            }
        } catch (\Throwable $e) {
            // Database lokal offline / tidak ada koneksi
        }

        // 2. Fallback: Ambil data user dari sesi (mode full API / database-less)
        $sessionUser = session('logged_user');
        if ($sessionUser && is_array($sessionUser)) {
            $user = new User();
            $user->forceFill($sessionUser);
            $user->exists = true;
            return $user;
        }

        return null;
    }
}

