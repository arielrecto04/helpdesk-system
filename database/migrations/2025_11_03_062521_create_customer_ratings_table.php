<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained('tickets')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('assigned_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('team_id')->nullable()->constrained('helpdesk_teams')->nullOnDelete();
            
            $table->tinyInteger('rating'); // Validation (1-5) should be in your application logic
            $table->text('comment')->nullable();
            $table->timestamp('submitted_on')->useCurrent();

            // Track when the rating record was created/updated
            $table->timestamps();

            // A customer can only rate a ticket once
            $table->unique(['ticket_id', 'customer_id'], 'uk_ticket_customer');

            // Helpful indexes for common filters/reports
            $table->index('ticket_id');
            $table->index('customer_id');
            $table->index('assigned_to_employee_id');
            $table->index('team_id');
            $table->index('rating');
            $table->index('submitted_on');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ratings');
    }
};