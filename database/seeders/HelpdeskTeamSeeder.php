<?php

namespace Database\Seeders;

use App\Models\HelpdeskTeam;
use Illuminate\Database\Seeder;

class HelpdeskTeamSeeder extends Seeder
{
    public function run(): void
    {
        $teams = [
            ['team_name' => 'Technical Support'],
            ['team_name' => 'Software Support'],
            ['team_name' => 'General Support']
        ];

        foreach ($teams as $team) {
            HelpdeskTeam::firstOrCreate($team);
        }
    }
}