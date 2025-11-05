<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['position_title' => 'Manager'],
            ['position_title' => 'Team Lead'],
            ['position_title' => 'Senior Developer'],
            ['position_title' => 'Junior Developer'],
            ['position_title' => 'Business Analyst'],
            ['position_title' => 'Project Manager'],
            ['position_title' => 'System Administrator'],
            ['position_title' => 'Network Engineer'],
            ['position_title' => 'Quality Assurance Engineer'],
            ['position_title' => 'Data Analyst'],
            ['position_title' => 'HR Specialist'],
            ['position_title' => 'Accountant'],
            ['position_title' => 'Marketing Specialist'],
            ['position_title' => 'Sales Representative']
        ];

        foreach ($positions as $position) {
            Position::create($position);
        }
    }
}