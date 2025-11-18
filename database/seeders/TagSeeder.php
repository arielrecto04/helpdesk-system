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
            'odoo 18',
            'odoo 19',
            'workspace',
            'laptop',
            'server',
            'printer',
            'network issue',
            'software bug',
            'password reset',
            'email problem'
        ];

        foreach ($tags as $tagName) {
           
            Tag::firstOrCreate(
                ['name' => $tagName]
            );
        }
    }
}