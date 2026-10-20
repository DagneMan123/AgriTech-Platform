<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ከ .env ፋይል ብቻ እንዲያነብ እናደርጋለን (ዲፎልት ጽሁፍ የለውም)
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        // ኢሜይል እና ፓስዎርድ ከ .env ካልተገኙ እንዳይበላሽ ቼክ እናደርጋለን
        if ($email && $password) {
            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => 'Admin User',
                    'password' => Hash::make($password),
                    'role' => 'admin',
                ]
            );
        }
    }
}