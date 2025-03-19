<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipient;

class RecipientsTableSeeder extends Seeder
{
    public function run()
    {
        $users = [
            ['name' => 'John Doe', 'department' => 'Admin', 'email' => 'johndoe@example.com'],
            ['name' => 'Jane Smith', 'department' => 'Admin', 'email' => 'janesmith@example.com'],
            ['name' => 'Alice Johnson', 'department' => 'Budget', 'email' => 'alicejohnson@example.com'],
            ['name' => 'Bob Brown', 'department' => 'Budget', 'email' => 'bobbrown@example.com'],
            ['name' => 'Charlie Davis', 'department' => 'Accounting', 'email' => 'charliedavis@example.com'],
            ['name' => 'Diana Evans', 'department' => 'Accounting', 'email' => 'dianaevans@example.com'],
            ['name' => 'Eve Foster', 'department' => 'Supply', 'email' => 'evefoster@example.com'],
            ['name' => 'Frank Green', 'department' => 'Supply', 'email' => 'frankgreen@example.com'],
            ['name' => 'Grace Hall', 'department' => 'BACS', 'email' => 'gracehall@example.com'],
            ['name' => 'Hank Irving', 'department' => 'BACS', 'email' => 'hankirving@example.com'],
            ['name' => 'Ivy Jones', 'department' => 'Cashier', 'email' => 'ivyjones@example.com'],
            ['name' => 'Jack King', 'department' => 'Cashier', 'email' => 'jackking@example.com'],
            ['name' => 'Karen Lee', 'department' => 'Registrar', 'email' => 'karenlee@example.com'],
            ['name' => 'Leo Martinez', 'department' => 'Registrar', 'email' => 'leomartinez@example.com'],
            ['name' => 'Mona Newton', 'department' => 'Biology', 'email' => 'monanewton@example.com'],
            ['name' => 'Nina Owens', 'department' => 'Biology', 'email' => 'ninaowens@example.com'],
            ['name' => 'Oscar Perez', 'department' => 'Chemistry', 'email' => 'oscarperez@example.com'],
            ['name' => 'Paula Quinn', 'department' => 'Chemistry', 'email' => 'paulaquinn@example.com'],
            ['name' => 'Quincy Reed', 'department' => 'CS/IT', 'email' => 'quincyreed@example.com'],
            ['name' => 'Rachel Scott', 'department' => 'CS/IT', 'email' => 'rachelscott@example.com'],
            ['name' => 'Steve Taylor', 'department' => 'Phys/Met', 'email' => 'stevetaylor@example.com'],
            ['name' => 'Tina Underwood', 'department' => 'Phys/Met', 'email' => 'tinaunderwood@example.com'],
            ['name' => 'Uma Vance', 'department' => 'Math', 'email' => 'umavance@example.com'],
            ['name' => 'Victor White', 'department' => 'Math', 'email' => 'victorwhite@example.com'],
            ['name' => 'Wendy Xavier', 'department' => 'Comp Lab', 'email' => 'wendyxavier@example.com'],
            ['name' => 'Xander Young', 'department' => 'Comp Lab', 'email' => 'xanderyoung@example.com'],
            ['name' => 'Yara Zimmerman', 'department' => 'NatSci Lab', 'email' => 'yarazimmerman@example.com'],
            ['name' => 'Zack Adams', 'department' => 'NatSci Lab', 'email' => 'zackadams@example.com'],
            ['name' => 'Amy Baker', 'department' => 'Others', 'email' => 'amybaker@example.com'],
            ['name' => 'Brad Clark', 'department' => 'Others', 'email' => 'bradclark@example.com'],
        ];

        foreach ($users as $user) {
            Recipient::create($user);
        }
    }
}