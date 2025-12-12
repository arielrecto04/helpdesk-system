<?php

namespace Database\Seeders;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $departmentPositions = [
            
            'Information Technology' => [
                'IT Manager',
                'Team Lead',
                'Senior Developer',
                'Junior Developer',
                'System Administrator',
                'Software Support',
                'Technical Support Specialist',
                'Quality Assurance Engineer',
                'Data Analyst'
            ],
            'Human Resources' => [
                'HR Manager',
                'HR Specialist',
            ],
            'Finance' => [
                'Finance Manager',
                'Accountant',
            ],
            'Sales & Marketing' => [
                'Sales Manager',
                'Marketing Specialist',
                'Sales Representative'
            ],
            'Operations' => [
                'Operations Manager',
                'Project Manager',
                'Business Analyst',
            ]
        ];

        foreach ($departmentPositions as $departmentName => $positions) {
            $department = Department::where('department_name', $departmentName)->first();

            if ($department) {
                foreach ($positions as $positionTitle) {
                    Position::firstOrCreate([
                        'department_id' => $department->id,
                        'position_title' => $positionTitle,
                    ]);
                }
            } else {
                $this->command->warn("Department not found: '$departmentName'. Skipping positions under it.");
            }
        }
    }
}