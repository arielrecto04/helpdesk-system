<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['department_name' => 'Information Technology'],
            ['department_name' => 'Human Resources'],
            ['department_name' => 'Finance'],
            ['department_name' => 'Marketing'],
            ['department_name' => 'Operations'],
            ['department_name' => 'Sales & Marketing'],
            ['department_name' => 'Customer Service'],
            ['department_name' => 'Research and Development'],
        ];

        foreach ($departments as $department) {
            Department::create($department);
        }
    }
}