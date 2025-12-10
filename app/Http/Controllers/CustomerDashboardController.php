<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use App\Models\Customer;
use App\Models\Tag;

class CustomerDashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $customer = $user ? $user->customer : null;

        $tickets = Ticket::query()
            ->when($customer, function ($q) use ($customer) {
                $q->where('customer_id', $customer->id);
            }, function ($q) {
                $q->whereNull('id');
            })
            ->with(['team', 'assignedTo', 'tags'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // Provide teams and priorities for the customer-facing create form
        $teams = HelpdeskTeam::all(['id', 'team_name']);
        $priorities = ['Low', 'Medium', 'High', 'Urgent'];
        $tags = Tag::all(['id', 'name']);

        return Inertia::render('Customer_Dashboard', [
            'tickets' => $tickets,
            'pageTitle' => 'My Sent Tickets',
            'teams' => $teams,
            'priorities' => $priorities,
            'tags' => $tags,
            'customer_id' => $customer ? $customer->id : null,
        ]);
    }

    /**
     * Show a specific ticket to the customer using the customer-facing view.
     */
    public function showTicket(Ticket $ticket)
    {
        $user = Auth::user();
        $customer = $user ? $user->customer : null;

        // Only allow the customer who created the ticket to view it (or admins via other routes)
        if (! $customer || $ticket->customer_id !== $customer->id) {
            abort(403, 'Unauthorized.');
        }

        $ticket->load(['team', 'assignedTo', 'customer', 'tags']);

        return Inertia::render('Customer_Dashboard/Show', [
            'ticket' => $ticket,
        ]);
    }
    /**
     * Store a ticket submitted by a customer through the customer dashboard.
     */
    public function storeTicket(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $customer = $user ? $user->customer : null;

        if (! $customer) {
            $userModel = $user;

            // Try to find an existing customer by email and link it to the user
            if ($userModel && $userModel->email) {
                $existing = Customer::where('email', $userModel->email)->first();
                if ($existing) {
                    $existing->user_id = $userModel->id;
                    $existing->first_name = $existing->first_name ?: $userModel->first_name;
                    $existing->middle_name = $existing->middle_name ?: $userModel->middle_name;
                    $existing->last_name = $existing->last_name ?: $userModel->last_name;
                    $existing->save();
                    $customer = $existing;
                } else {
                    $customer = Customer::create([
                        'user_id' => $userModel->id,
                        'first_name' => $userModel->first_name ?? null,
                        'middle_name' => $userModel->middle_name ?? null,
                        'last_name' => $userModel->last_name ?? null,
                        'email' => $userModel->email ?? null,
                    ]);
                }
            } else {
                $customer = Customer::create([
                    'user_id' => null,
                    'first_name' => $userModel->first_name ?? null,
                    'middle_name' => $userModel->middle_name ?? null,
                    'last_name' => $userModel->last_name ?? null,
                    'email' => $userModel->email ?? null,
                ]);
            }
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['Low', 'Medium', 'High', 'Urgent'])],
            'team_id' => 'required|exists:helpdesk_teams,id',
            'deadline' => 'nullable|date',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'exists:tags,id',
        ]);

        $data = array_merge($validated, [
            'customer_id' => $customer->id,
            // customers must not assign support user
            'assigned_to_employee_id' => null,
            'stage' => 'Open',
        ]);

        $ticket = Ticket::create($data);
        // attach tags if provided
        $ticket->tags()->sync($request->input('tag_ids', []));

        return redirect()->route('customer.dashboard')->with('message', 'Ticket created successfully.');
    }
}
