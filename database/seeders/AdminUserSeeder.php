<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'id_number' => '0000-0000-00000',
            'password' => bcrypt('SecurePassword123'),
            'department' => 'Admin'
        ]);
    }
} 