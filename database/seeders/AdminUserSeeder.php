<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\DefaultAdminCredentials;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::query()
            ->where('email', DefaultAdminCredentials::EMAIL)
            ->orWhere('email', 'admin@pemudacirengit.test')
            ->first();

        $payload = [
            'name' => 'Administrator',
            'email' => DefaultAdminCredentials::EMAIL,
            'email_verified_at' => now(),
            'password' => Hash::make(DefaultAdminCredentials::PASSWORD),
            'role' => 'admin',
            'member_id' => null,
        ];

        if ($admin) {
            $admin->forceFill($payload)->save();

            return;
        }

        User::create($payload);
    }
}
