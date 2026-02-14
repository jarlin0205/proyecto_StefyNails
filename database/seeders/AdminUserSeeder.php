<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name' => 'Administrador',
                'password' => Hash::make('admin123'), // Weak password for dev, user should change it via reset
                'email_verified_at' => now(),
            ]
        );
    }
}
