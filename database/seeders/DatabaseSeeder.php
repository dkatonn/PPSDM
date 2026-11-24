<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RuangLingkup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $email = env('SIPENA_SUPER_ADMIN_EMAIL');
        $password = env('SIPENA_SUPER_ADMIN_PASSWORD');

        if (! $email || ! $password || strlen($password) < 12) {
            throw new \RuntimeException(
                'Set SIPENA_SUPER_ADMIN_EMAIL and SIPENA_SUPER_ADMIN_PASSWORD (minimum 12 characters) in .env before seeding.'
            );
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('SIPENA_SUPER_ADMIN_NAME', 'Super Admin SIPENA'),
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
            ],
        );

        foreach (['Magang', 'Laboratorium', 'Perkuliahan', 'Pertukaran Data'] as $scope) {
            RuangLingkup::firstOrCreate(['name' => $scope]);
        }
    }
}
