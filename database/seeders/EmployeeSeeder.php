<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Department;
use App\Models\Company;
use App\Models\User;
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
                'first_name' => 'Johnny',
                'middle_name' => null,
                'last_name' => 'Bravo',
                'email' => 'johnny.bravo@example.com',
                'phone_number' => '0917-000-0001'
            ],
            [
                'first_name' => 'Tony',
                'middle_name' => 'Edward',
                'last_name' => 'Stark',
                'email' => 'tony.stark@example.com',
                'phone_number' => '0917-000-0002'
            ],
           
        ];

        foreach ($employeesData as $data) {

            $department = $departments->random();
            $position = $department->positions->random();
            $employee = Employee::where('email', $data['email'])->first();
            
            if ($employee) {
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

        $agentUsers = User::whereHas('roles', function($query) {
            $query->where('name', 'agent');
        })->get();

        foreach ($agentUsers as $user) {
            $employee = Employee::where('email', $user->email)->first();
            if ($employee) {
                $employee->update(['user_id' => $user->id]);
            }
        }
    }
}