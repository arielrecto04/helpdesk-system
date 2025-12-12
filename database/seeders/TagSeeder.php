<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tag;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{

    public function run(): void
    {

        $tags = [
            'Odoo 18',
            'Odoo 19',
            'Workspace',
            'Laptop',
            'Server',
            'Printer',
            'Network Issue',
            'Software Bug',
            'Password Reset',
            'Email Problem'
        ];

        foreach ($tags as $tagName) {
           
            Tag::firstOrCreate(
                ['name' => $tagName]
            );
        }
    }
}