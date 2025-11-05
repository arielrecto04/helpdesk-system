<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::all();
        $positions = Position::all();

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
            $customer['department_id'] = $departments->random()->id;
            $customer['position_id'] = $positions->random()->id;
            
            Customer::create($customer);
        }
    }
}