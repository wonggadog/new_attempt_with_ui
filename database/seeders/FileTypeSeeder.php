<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\FileType;

class FileTypeSeeder extends Seeder
{
    public function run()
    {
        $fileTypes = [
            'Memos',
            'Reports',
            'Financial Documents',
            'Student Records'
        ];

        foreach ($fileTypes as $type) {
            FileType::create(['name' => $type]);
        }
    }
} 