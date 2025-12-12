<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- EXISTING IITS BRANCHES ---

        Company::firstOrCreate(
            ['name' => 'IITS (Laguna)'],
            [
                'address' => 'Laguna International Industrial Park (LIIP), Mamplasan, Biñan, Laguna, Philippines',
                'phone' => '555-1234',
                'email' => 'contact@iitslaguna.com'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'IITS (Parañaque)'],
            [
                'address' => 'Km. 18, West Service Road, Parañaque City, Philippines',
                'phone' => '555-5678',
                'email' => 'info@iitsparanaque.com'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'IITS (Cebu)'],
            [
                'address' => 'Cebu Business Park, Cebu City, Philippines',
                'phone' => '555-8765',
                'email' => 'info@iitscebu.com'
            ]
        );

        // --- NEW CLIENT COMPANIES ---

        Company::firstOrCreate(
            ['name' => 'Gardenia Bakeries Philippines Inc.'],
            [
                'address' => 'Laguna International Industrial Park (LIIP), Mamplasan, Biñan, Laguna',
                'phone' => '(02) 8889-8888',
                'email' => 'customer_relations@gardenia.com.ph'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'Sugar Dolls'],
            [
                'address' => 'Quezon City, Metro Manila, Philippines',
                'phone' => '(02) 8374-1234',
                'email' => 'inquiry@sugardolls.ph'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'WevMedical'],
            [
                'address' => 'Sta. Cruz, Manila, Philippines',
                'phone' => '(02) 8711-5678',
                'email' => 'sales@wevmedical.com'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'Sterilab'],
            [
                'address' => '123 Caloocan Industrial Subd., Caloocan City, Philippines',
                'phone' => '(02) 8364-9999',
                'email' => 'support@sterilab.com.ph'
            ]
        );
    }
}