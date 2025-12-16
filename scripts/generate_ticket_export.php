<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Exports\TicketAnalysisExport;
use App\Models\Ticket;
use App\Models\User;

$user = User::find(1);
if (! $user) {
    echo "User ID 1 not found\n";
    exit(1);
}

$tickets = Ticket::query()->visibleTo($user)->get();

try {
    app('excel')->store(new TicketAnalysisExport($tickets), 'ticket-analysis-test.xlsx');
    echo "Stored export to storage/app/ticket-analysis-test.xlsx\n";
} catch (Throwable $e) {
    echo "Export failed: " . $e->getMessage() . "\n";
    exit(1);
}
