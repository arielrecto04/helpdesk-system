<?php

namespace App\Http\Controllers;

use App\Models\CustomerRating;
use App\Models\Ticket;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\HelpdeskTeam;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CustomerRatingsController extends Controller
{
    /**
     * Display a listing of customer ratings.
     */
    public function index(Request $request): Response
    {
        $query = CustomerRating::with(['ticket', 'customer', 'assignedTo', 'team'])
            ->orderBy('submitted_on', 'desc');

        // Apply filters
        if ($request->has('rating') && $request->rating !== 'all') {
            $query->where('rating', $request->rating);
        }

        if ($request->has('team_id') && $request->team_id !== 'all') {
            $query->where('team_id', $request->team_id);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhereHas('ticket', function ($ticketQuery) use ($search) {
                    $ticketQuery->where('subject', 'like', "%{$search}%")
                        ->orWhere('id', 'like', "%{$search}%");
                })
                ->orWhere('comment', 'like', "%{$search}%");
            });
        }

        $ratings = $query->paginate(15)->withQueryString();

        // Map the ratings data
        $ratingsData = $ratings->through(function ($rating) {
            return [
                'id' => $rating->id,
                'ticket_id' => $rating->ticket_id,
                'ticket_subject' => $rating->ticket ? $rating->ticket->subject : 'N/A',
                'customer_name' => $rating->customer 
                    ? $rating->customer->first_name . ' ' . $rating->customer->last_name 
                    : 'N/A',
                'customer_email' => $rating->customer ? $rating->customer->email : 'N/A',
                'assigned_to' => $rating->assignedTo 
                    ? $rating->assignedTo->first_name . ' ' . $rating->assignedTo->last_name 
                    : 'Unassigned',
                'team_name' => $rating->team ? $rating->team->team_name : 'N/A',
                'rating' => $rating->rating,
                'comment' => $rating->comment,
                'submitted_on' => $rating->submitted_on->format('Y-m-d H:i'),
                'created_at' => $rating->created_at->format('Y-m-d H:i'),
            ];
        });

        // Get filter options
        $teams = HelpdeskTeam::orderBy('team_name')->get(['id', 'team_name']);

        // Calculate statistics
        $stats = [
            'total' => CustomerRating::count(),
            'average' => round(CustomerRating::avg('rating') ?? 0, 1),
            'five_star' => CustomerRating::where('rating', 5)->count(),
            'four_star' => CustomerRating::where('rating', 4)->count(),
            'three_star' => CustomerRating::where('rating', 3)->count(),
            'two_star' => CustomerRating::where('rating', 2)->count(),
            'one_star' => CustomerRating::where('rating', 1)->count(),
        ];

        return Inertia::render('Customer_Ratings', [
            'ratings' => $ratingsData,
            'teams' => $teams,
            'stats' => $stats,
            'filters' => [
                'search' => $request->search,
                'rating' => $request->rating ?? 'all',
                'team_id' => $request->team_id ?? 'all',
            ],
        ]);
    }

    /**
     * Display the specified customer rating.
     */
    public function show(CustomerRating $rating): Response
    {
        $rating->load(['ticket.tags', 'customer', 'assignedTo', 'team']);

        $ratingData = [
            'id' => $rating->id,
            'ticket' => [
                'id' => $rating->ticket->id,
                'subject' => $rating->ticket->subject,
                'description' => $rating->ticket->description,
                'priority' => $rating->ticket->priority,
                'stage' => $rating->ticket->stage,
                'created_at' => $rating->ticket->created_at->format('Y-m-d H:i'),
                'updated_at' => $rating->ticket->updated_at->format('Y-m-d H:i'),
                'deadline' => $rating->ticket->deadline,
                'tags' => $rating->ticket->tags->map(fn($tag) => $tag->name),
            ],
            'customer' => [
                'id' => $rating->customer->id,
                'name' => $rating->customer->first_name . ' ' . $rating->customer->last_name,
                'email' => $rating->customer->email,
                'phone' => $rating->customer->phone_number,
                'company' => $rating->customer->company ? $rating->customer->company->name : 'N/A',
            ],
            'assigned_to' => $rating->assignedTo ? [
                'id' => $rating->assignedTo->id,
                'name' => $rating->assignedTo->first_name . ' ' . $rating->assignedTo->last_name,
                'email' => $rating->assignedTo->email,
            ] : null,
            'team' => $rating->team ? [
                'id' => $rating->team->id,
                'name' => $rating->team->team_name,
            ] : null,
            'rating' => $rating->rating,
            'comment' => $rating->comment,
            'submitted_on' => $rating->submitted_on->format('F d, Y \a\t h:i A'),
            'created_at' => $rating->created_at->format('Y-m-d H:i'),
        ];

        // Get other ratings from the same customer
        $otherRatings = CustomerRating::where('customer_id', $rating->customer_id)
            ->where('id', '!=', $rating->id)
            ->with(['ticket', 'team'])
            ->orderBy('submitted_on', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                return [
                    'id' => $r->id,
                    'ticket_id' => $r->ticket_id,
                    'ticket_subject' => $r->ticket ? $r->ticket->subject : 'N/A',
                    'team_name' => $r->team ? $r->team->team_name : 'N/A',
                    'rating' => $r->rating,
                    'submitted_on' => $r->submitted_on->format('M d, Y'),
                ];
            });

        return Inertia::render('Customer_Ratings/Show', [
            'rating' => $ratingData,
            'otherRatings' => $otherRatings,
        ]);
    }
}
