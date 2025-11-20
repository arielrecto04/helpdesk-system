<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Company;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $companyIds = Company::pluck('id');
        $departments = Department::whereHas('positions')->with('positions')->get();
        if ($departments->isEmpty()) {
            $this->command->warn('No departments with positions found. Skipping EmployeeSeeder.');
            return;
        }
        if ($companyIds->isEmpty()) {
            $this->command->error('Walang nahanap na kumpanya. Paki-run muna ang CompanySeeder.');
            return;
        }

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

            $employee = Employee::where('email', $data['email'])->first();
            
            if ($employee) {
                // Update existing employee without changing employee_code
                $employee->update([
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'company_id' => $companyIds->random()
                ]);
            } else {
                // Create new employee with unique code
                $maxId = Employee::max('id') ?? 0;
                Employee::create([
                    'email' => $data['email'],
                    'first_name' => $data['first_name'],
                    'middle_name' => $data['middle_name'] ?? null,
                    'last_name' => $data['last_name'],
                    'phone_number' => $data['phone_number'],
                    'department_id' => $department->id,
                    'position_id' => $position->id,
                    'hire_date' => now()->subDays(rand(30, 1000)),
                    'employee_code' => 'EMP-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT),
                    'company_id' => $companyIds->random()
                ]);
            }
        }
    }
}