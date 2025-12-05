<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\HelpdeskTeam;

class EmployeeHelpdeskTeamSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teams = HelpdeskTeam::all()->pluck('id')->toArray();

        if (empty($teams)) {
            return;
        }

        Employee::all()->each(function (Employee $employee) use ($teams) {
            $max = min(3, count($teams));
            $n = rand(1, $max);
            $picked = collect($teams)->random($n)->toArray();
            $employee->helpdeskTeams()->syncWithoutDetaching($picked);
        });
    }
}
