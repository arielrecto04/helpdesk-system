<?php

namespace App\Exports;

/**
 * Ticket Analysis Export Class
 * Provides methods for exporting ticket data to CSV/Excel formats
 */
class TicketAnalysisExport
{
    protected $tickets;

    public function __construct($tickets)
    {
        $this->tickets = $tickets;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Subject',
            'Customer',
            'Team',
            'Assigned To',
            'Priority',
            'Stage',
            'Deadline',
            'Created At',
        ];
    }

    public function map($ticket): array
    {
        $customerName = 'N/A';
        if ($ticket->customer) {
            if ($ticket->customer->company) {
                $customerName = $ticket->customer->company->name;
            } else {
                $customerName = trim($ticket->customer->first_name . ' ' . $ticket->customer->last_name);
            }
        }

        return [
            $ticket->id,
            $ticket->subject,
            $customerName,
            $ticket->team?->team_name ?? 'Unassigned',
            ($ticket->assignedEmployee ? trim($ticket->assignedEmployee->first_name . ' ' . $ticket->assignedEmployee->last_name) : ''),
            $ticket->priority,
            $ticket->stage,
            ($ticket->deadline ? $ticket->deadline->format('Y-m-d H:i') : ''),
            $ticket->created_at->format('Y-m-d H:i'),
        ];
    }
}
