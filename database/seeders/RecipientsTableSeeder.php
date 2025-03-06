<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Recipient;

class RecipientsTableSeeder extends Seeder
{
    public function run()
    {
        $recipients = [
            ['name' => 'John Doe', 'department' => 'Admin'],
            ['name' => 'Jane Smith', 'department' => 'Admin'],
            ['name' => 'Alice Johnson', 'department' => 'Budget'],
            ['name' => 'Bob Brown', 'department' => 'Budget'],
            ['name' => 'Charlie Davis', 'department' => 'Accounting'],
            ['name' => 'Diana Evans', 'department' => 'Accounting'],
            ['name' => 'Eve Foster', 'department' => 'Supply'],
            ['name' => 'Frank Green', 'department' => 'Supply'],
            ['name' => 'Grace Hall', 'department' => 'BACS'],
            ['name' => 'Hank Irving', 'department' => 'BACS'],
            ['name' => 'Ivy Jones', 'department' => 'Cashier'],
            ['name' => 'Jack King', 'department' => 'Cashier'],
            ['name' => 'Karen Lee', 'department' => 'Registrar'],
            ['name' => 'Leo Martinez', 'department' => 'Registrar'],
            ['name' => 'Mona Newton', 'department' => 'Biology'],
            ['name' => 'Nina Owens', 'department' => 'Biology'],
            ['name' => 'Oscar Perez', 'department' => 'Chemistry'],
            ['name' => 'Paula Quinn', 'department' => 'Chemistry'],
            ['name' => 'Quincy Reed', 'department' => 'CS/IT'],
            ['name' => 'Rachel Scott', 'department' => 'CS/IT'],
            ['name' => 'Steve Taylor', 'department' => 'Phys/Met'],
            ['name' => 'Tina Underwood', 'department' => 'Phys/Met'],
            ['name' => 'Uma Vance', 'department' => 'Math'],
            ['name' => 'Victor White', 'department' => 'Math'],
            ['name' => 'Wendy Xavier', 'department' => 'Comp Lab'],
            ['name' => 'Xander Young', 'department' => 'Comp Lab'],
            ['name' => 'Yara Zimmerman', 'department' => 'NatSci Lab'],
            ['name' => 'Zack Adams', 'department' => 'NatSci Lab'],
            ['name' => 'Amy Baker', 'department' => 'Others'],
            ['name' => 'Brad Clark', 'department' => 'Others'],
        ];

        foreach ($recipients as $recipient) {
            Recipient::create($recipient);
        }
    }
}
