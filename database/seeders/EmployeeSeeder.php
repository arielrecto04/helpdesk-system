<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
// Hindi na natin kailangan ang User o Hash dito
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        // Kunin ang departments na MAY positions
        $departments = Department::whereHas('positions')->with('positions')->get();

        if ($departments->isEmpty()) {
            $this->command->warn('No departments with positions found. Skipping EmployeeSeeder.');
            return;
        }

        // Ito na ngayon ang data para sa 'employees' table
        $employeesData = [
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

        foreach ($employeesData as $data) {

            $department = $departments->random();
            $position = $department->positions->random();

            Employee::updateOrCreate(
                ['email' => $data['email']],
                [
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'hire_date' => now()->subDays(rand(30, 1000)),
                    'employee_code' => 'EMP-' . str_pad(Employee::max('id') + 1, 4, '0', STR_PAD_LEFT)
                ]
            );
        }
    }
}