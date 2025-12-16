<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check Admin user
$adminUser = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Admin');
})->first();

if (!$adminUser) {
    echo "❌ No Admin user found!\n";
    exit(1);
}

echo "✅ Admin User: {$adminUser->email}\n";
echo "   Name: {$adminUser->first_name} {$adminUser->last_name}\n\n";

// Check permissions
$perms = [
    'can_view_other_locations_tickets',
    'can_view_other_teams_tickets', 
    'can_view_other_users_tickets'
];

echo "📋 Permissions:\n";
foreach ($perms as $perm) {
    $has = $adminUser->hasPermissionTo($perm);
    echo "   " . ($has ? '✅' : '❌') . " {$perm}\n";
}

// Check Employee record
echo "\n👤 Employee Record:\n";
$employee = \App\Models\Employee::where('user_id', $adminUser->id)
    ->orWhere('email', $adminUser->email)
    ->first();

if ($employee) {
    echo "   ✅ Found: {$employee->first_name} {$employee->last_name}\n";
    echo "   Company ID: " . ($employee->company_id ?? 'null') . "\n";
    echo "   Teams: " . $employee->helpdeskTeams->pluck('team_name')->implode(', ') . "\n";
} else {
    echo "   ❌ No Employee record linked!\n";
}

// Check tickets
echo "\n🎫 Tickets:\n";
$totalTickets = \App\Models\Ticket::count();
$visibleTickets = \App\Models\Ticket::query()->visibleTo($adminUser)->count();

echo "   Total in DB: {$totalTickets}\n";
echo "   Visible to Admin: {$visibleTickets}\n";

if ($totalTickets > 0 && $visibleTickets === 0) {
    echo "\n⚠️  PROBLEM: Admin cannot see any tickets!\n";
    if (!$employee) {
        echo "   → No Employee record - visibleTo() requires one\n";
    }
}
