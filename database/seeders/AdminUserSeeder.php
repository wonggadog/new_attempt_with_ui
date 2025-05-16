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
            'email' => 'admin@bucsdocumanage.local',
            'id_number' => '0000-0000-00001',
            'password' => bcrypt('bucsdocumanage#2025'),
            'department' => 'Admin'
        ]);
    }
} 