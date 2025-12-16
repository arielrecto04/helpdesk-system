<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate the TicketAnalysisController logic
$user = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Admin');
})->first();

if (!$user) {
    echo "❌ No Admin user found!\n";
    exit(1);
}

echo "🔍 Testing Ticket Analysis Controller Logic\n\n";
echo "Admin: {$user->email}\n\n";

// Build visible tickets query (same as controller)
$visibleTicketsQuery = \App\Models\Ticket::query()->visibleTo($user);
$visibleTicketIds = $visibleTicketsQuery->pluck('id')->toArray();

echo "📊 Visible Ticket IDs: " . count($visibleTicketIds) . " tickets\n";
echo "   IDs: " . implode(', ', array_slice($visibleTicketIds, 0, 10)) . (count($visibleTicketIds) > 10 ? '...' : '') . "\n\n";

// Stage stats (same query as controller)
$stageStats = \App\Models\Ticket::whereIn('id', $visibleTicketIds)
    ->select(
        \Illuminate\Support\Facades\DB::raw('COUNT(*) as total'),
        \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN stage = 'Open' THEN 1 ELSE 0 END) as open"),
        \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN stage = 'In Progress' THEN 1 ELSE 0 END) as in_progress"),
        \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN stage = 'Resolved' THEN 1 ELSE 0 END) as resolved"),
        \Illuminate\Support\Facades\DB::raw("SUM(CASE WHEN stage = 'Closed' THEN 1 ELSE 0 END) as closed")
    )
    ->first();

$stats = [
    'total' => $stageStats->total ?? 0,
    'open' => $stageStats->open ?? 0,
    'inProgress' => $stageStats->in_progress ?? 0,
    'resolved' => $stageStats->resolved ?? 0,
    'closed' => $stageStats->closed ?? 0,
];

echo "📈 Stats that will be sent to Vue:\n";
echo "   Total: {$stats['total']}\n";
echo "   Open: {$stats['open']}\n";
echo "   In Progress: {$stats['inProgress']}\n";
echo "   Resolved: {$stats['resolved']}\n";
echo "   Closed: {$stats['closed']}\n\n";

if ($stats['total'] > 0) {
    echo "✅ SUCCESS! The page SHOULD show data (hasData = true)\n";
} else {
    echo "❌ PROBLEM! The page will show 'No Ticket Data Available'\n";
}
