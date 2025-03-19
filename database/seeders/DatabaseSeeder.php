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
            // First argument: Attributes to search for
            ['email' => 'aldiangeldioquino.wong@bicol-u.edu.ph'],
            // Second argument: Attributes to update or create
            [
                'name' => 'Al Dian Gel Wong',
                'email' => 'aldiangeldioquino.wong@bicol-u.edu.ph',
                'email_verified_at' => now(),
                'id_number' => '2021-2401-23944',
                'password' => bcrypt('BicolUni2021'), // Always hash passwords
                'department' => 'Admin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        User::updateOrCreate(
            // First argument: Attributes to search for
            ['email' => 'menardmonreal.miraballes@bicol-u.edu.ph'],
            // Second argument: Attributes to update or create
            [
                'name' => 'Menard Miraballes',
                'email' => 'menardmonreal.miraballes@bicol-u.edu.ph',
                'email_verified_at' => now(),
                'id_number' => '2021-2882-88633',
                'password' => bcrypt('BicolUni2021'), // Always hash passwords
                'department' => 'Admin',
                'remember_token' => Str::random(10),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->call(RecipientsTableSeeder::class);
    }
}