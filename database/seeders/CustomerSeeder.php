<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Position;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = Company::pluck('id');
         if ($companyIds->isEmpty()) {
            $this->command->error('Walang nahanap na kumpanya. Paki-run muna ang CompanySeeder.');
            return;
        }
        
        $customers = [
            [
                'first_name' => 'John',
                'middle_name' => 'Robert',
                'last_name' => 'Smith',
                'email' => 'john.smith@company.com',
                'phone_number' => '123-456-7890'
            ],
            [
                'first_name' => 'Maria',
                'middle_name' => 'Roberta',
                'last_name' => 'Garcia',
                'email' => 'maria.garcia@company.com',
                'phone_number' => '123-456-7891'
            ],
            [
                'first_name' => 'David',
                'middle_name' => 'James',
                'last_name' => 'Johnson',
                'email' => 'david.johnson@company.com',
                'phone_number' => '123-456-7892'
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Williams',
                'email' => 'sarah.williams@company.com',
                'phone_number' => '123-456-7893'
            ],
            [
                'first_name' => 'Michael',
                'middle_name' => 'Thomas',
                'last_name' => 'Brown',
                'email' => 'michael.brown@company.com',
                'phone_number' => '123-456-7894'
            ]
        ];

        foreach ($customers as $customer) {
            $customer['company_id'] = $companyIds->random();
            // If the customers table has a user_id column, try to link by email
            if (Schema::hasColumn('customers', 'user_id')) {
                $user = User::where('email', $customer['email'])->first();
                $customer['user_id'] = $user ? $user->id : null;
            }

            Customer::updateOrCreate([
                'email' => $customer['email'],
            ], $customer);
        }
    }
}