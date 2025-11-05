<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\HelpdeskTeam;
use App\Models\Ticket;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTicketsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles if they don't exist
        if (!Role::where('name', 'admin')->exists()) {
            Role::create(['name' => 'admin']);
        }
        if (!Role::where('name', 'agent')->exists()) {
            Role::create(['name' => 'agent']);
        }
    }

    public function test_team_tickets_page_is_displayed_for_admin(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $team = HelpdeskTeam::factory()->create();

        $response = $this->actingAs($admin)->get(route('team.tickets', $team->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('TeamTickets'));
        $response->assertInertia(fn ($page) => $page->has('tickets'));
    }

    public function test_team_tickets_page_is_displayed_for_team_member(): void
    {
        $user = User::factory()->create();
        $team = HelpdeskTeam::factory()->create();
        $user->teams()->attach($team);

        $response = $this->actingAs($user)->get(route('team.tickets', $team->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->component('TeamTickets'));
        $response->assertInertia(fn ($page) => $page->has('tickets'));
    }

    public function test_team_tickets_page_is_not_displayed_for_non_team_member(): void
    {
        $user = User::factory()->create();
        $team = HelpdeskTeam::factory()->create();
        // User is not attached to the team

        $response = $this->actingAs($user)->get(route('team.tickets', $team->id));

        $response->assertForbidden();
    }

    public function test_tickets_data_is_correctly_loaded(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $admin = User::factory()->create();
        $admin->roles()->attach($adminRole);

        $team = HelpdeskTeam::factory()->create();
        $assignedUser = User::factory()->create();

        // Create tickets for the team
        Ticket::factory()->count(3)->create([
            'team_id' => $team->id,
            'assigned_to_user_id' => $assignedUser->id,
        ]);
        Ticket::factory()->count(2)->create([
            'team_id' => $team->id,
            'assigned_to_user_id' => null,
        ]);

        $response = $this->actingAs($admin)->get(route('team.tickets', $team->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('TeamTickets')
            ->has('tickets.data', 5)
            ->where('tickets.data.0.assigned_to.first_name', $assignedUser->first_name)
            ->where('tickets.data.0.assigned_to.last_name', $assignedUser->last_name)
            ->where('tickets.data.4.assigned_to', null) // Assuming the last ticket is unassigned
        );
    }
}
