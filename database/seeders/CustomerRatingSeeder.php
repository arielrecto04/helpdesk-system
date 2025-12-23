<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CustomerRating;
use App\Models\Ticket;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerRatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $baseQuery = Ticket::query()
            ->whereIn('stage', ['Resolved', 'Closed'])
            ->with(['customer', 'assignedEmployee', 'team']);

        $ticketCount = (clone $baseQuery)->count();

        if ($ticketCount === 0) {
            $this->command->info('No resolved/closed tickets found. Please seed tickets first.');
            return;
        }

        $this->command->info('Seeding customer ratings...');

        $ratingComments = [
            5 => [
                'Excellent service! The issue was resolved quickly and professionally.',
                'Outstanding support! Very satisfied with the service.',
                'Amazing experience! The agent was very helpful and knowledgeable.',
                'Perfect! Issue resolved on first contact. Highly recommended.',
                'Superb service! Quick response and excellent communication.',
            ],
            4 => [
                'Great service! Minor delay but issue was resolved satisfactorily.',
                'Very good experience. The agent was helpful and professional.',
                'Good service overall. Issue resolved as expected.',
                'Satisfied with the service. Agent was knowledgeable.',
                'Good response time and helpful support.',
            ],
            3 => [
                'Average service. Issue was resolved but took longer than expected.',
                'Okay experience. The solution worked but communication could be better.',
                'Acceptable service. Issue resolved eventually.',
                'Fair service. Expected better communication.',
                'Average experience. Issue resolved but not impressed.',
            ],
            2 => [
                'Below expectations. Issue took too long to resolve.',
                'Not satisfied. Had to follow up multiple times.',
                'Poor communication. Issue eventually resolved but frustrating.',
                'Disappointing service. Expected faster resolution.',
                'Not happy with the service. Too many delays.',
            ],
            1 => [
                'Very poor service. Issue still not fully resolved.',
                'Terrible experience. No proper follow-up.',
                'Extremely disappointed. Had to escalate multiple times.',
                'Unacceptable service. Very frustrating experience.',
                'Very bad experience. Would not recommend.',
            ],
        ];

        // Rate ~65% of resolved tickets (at least 1) without loading everything into memory
        $targetCount = (int) floor($ticketCount * 0.65);
        $targetCount = max(1, min($ticketCount, $targetCount));

        $ticketsToRate = (clone $baseQuery)
            ->inRandomOrder()
            ->take($targetCount)
            ->get();

        foreach ($ticketsToRate as $ticket) {
            // Skip if already rated
            if (CustomerRating::where('ticket_id', $ticket->id)->exists()) {
                continue;
            }

            // Generate realistic rating distribution
            // 40% chance of 5 stars, 30% of 4 stars, 20% of 3 stars, 8% of 2 stars, 2% of 1 star
            $rand = rand(1, 100);
            if ($rand <= 40) {
                $rating = 5;
            } elseif ($rand <= 70) {
                $rating = 4;
            } elseif ($rand <= 90) {
                $rating = 3;
            } elseif ($rand <= 98) {
                $rating = 2;
            } else {
                $rating = 1;
            }

            // Get random comment for this rating
            $comment = $ratingComments[$rating][array_rand($ratingComments[$rating])];

            // Create rating 1-7 days after ticket was resolved/closed
            $submittedOn = Carbon::parse($ticket->updated_at)->addDays(rand(1, 7));

            CustomerRating::create([
                'ticket_id' => $ticket->id,
                'customer_id' => $ticket->customer_id,
                'employee_id' => $ticket->employee_id,
                'team_id' => $ticket->team_id,
                'rating' => $rating,
                'comment' => $comment,
                'submitted_on' => $submittedOn,
            ]);
        }

        $ratingCount = CustomerRating::count();
        $this->command->info("Successfully created {$ratingCount} customer ratings.");
        
        // Display rating distribution
        $distribution = CustomerRating::selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get();
        
        $this->command->info("\nRating Distribution:");
        foreach ($distribution as $item) {
            $stars = str_repeat('★', $item->rating);
            $this->command->info("  {$stars} ({$item->rating}): {$item->count} ratings");
        }
    }
}
