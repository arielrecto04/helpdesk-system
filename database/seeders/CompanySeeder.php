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
        Company::firstOrCreate(
            ['name' => 'Acme Corporation'],
            [
                'address' => '123 Main St, Anytown, USA',
                'phone' => '555-1234',
                'email' => 'contact@acme.com'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'Tech Solutions Inc.'],
            [
                'address' => '456 Tech Park, Silicon Valley, USA',
                'phone' => '555-5678',
                'email' => 'info@techsolutions.com'
            ]
        );

        Company::firstOrCreate(
            ['name' => 'Global Logistics'],
            [
                'address' => '789 Industrial Ave, Metropolis, USA',
                'phone' => '555-9012',
                'email' => 'support@globallogistics.com'
            ]
        );

        // Magdagdag pa ng ibang kumpanya kung kailangan
    }
}