<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create only ONE default admin (for emergency access)
      //  User::updateOrCreate(
      //      ['email' => 'admin@bicol-u.edu.ph'],
      //      [
      //          'name' => 'System Admin',
      //          'email' => 'admin@bicol-u.edu.ph',
      //          'id_number' => '0000-0000-00000', // Generic ID
      //          'password' => bcrypt('SecurePassword123'), // Change this!
      //          'department' => 'Admin',
      //          'email_verified_at' => now(),
      //          'remember_token' => Str::random(10),
      //      ]
      //  );

        //  No need for test users since admins can now add them via the dashboard

        $this->call([
            AdminUserSeeder::class,
            FileTypeSeeder::class,
            // ... other seeders ...
        ]);
    }
}