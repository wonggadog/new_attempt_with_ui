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
        //DB::table('users')->truncate();

        User::updateOrCreate(
            ['email' => 'aldiangeldioquino.wong@bicol-u.edu.ph'], // Prevent duplicate entry
            [
                'name' => 'Al Dian Gel Wong',
                'email_verified_at' => now(),
                // original code----- 'password' => bcrypt('password'),
                'id_number' => '2021-2401-23944',
                'password' => 'BicolUni2021',
                'department' => 'Admin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call(RecipientsTableSeeder::class);
    }
}
