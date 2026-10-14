<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin / Otto user
        User::updateOrCreate(
            ['username' => 'otto'],
            [
                'name' => 'Otto Parking',
                'email' => 'otto@otto.com',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'role' => 'Admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator',
                'email' => 'admin@otto.com',
                'password' => \Illuminate\Support\Facades\Hash::make('admin123'),
                'role' => 'Admin',
            ]
        );

        User::updateOrCreate(
            ['username' => 'operator'],
            [
                'name' => 'Operator Lokasi',
                'email' => 'operator@otto.com',
                'password' => \Illuminate\Support\Facades\Hash::make('operator123'),
                'role' => 'Operator',
            ]
        );

        // Seed monitoring data
        $this->call(MonitoringDataSeeder::class);
    }
}
