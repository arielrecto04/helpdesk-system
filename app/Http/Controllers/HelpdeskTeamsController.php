<?php

namespace App\Http\Controllers;

use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class HelpdeskTeamsController extends Controller
{
     public function index(): Response
    {
        return Inertia::render('HelpdeskTeams', [
            'teams' => HelpdeskTeam::all(),
        ]);
    }
}
