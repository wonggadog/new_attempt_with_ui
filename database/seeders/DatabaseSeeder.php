<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Delete existing users before seeding
        DB::table('users')->truncate();

        User::updateOrCreate(
            ['email' => 'test@example.com'], // Prevent duplicate entry
            [
                'name' => 'Test User',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call(RecipientsTableSeeder::class);
    }
}
