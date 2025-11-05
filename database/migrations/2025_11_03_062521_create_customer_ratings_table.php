<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Import DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('assigned_to_user_id')->nullable();
            $table->unsignedBigInteger('team_id')->nullable();
            
            $table->tinyInteger('rating'); // Validation (1-5) should be in your application logic
            $table->text('comment')->nullable();
            $table->timestamp('submitted_on')->default(DB::raw('CURRENT_TIMESTAMP'));

            // A customer can only rate a ticket once
            $table->unique(['ticket_id', 'customer_id'], 'uk_ticket_customer');

            // Foreign Keys
            $table->foreign('ticket_id')
                  ->references('id')
                  ->on('tickets')
                  ->onDelete('cascade'); // If ticket is deleted, rating is irrelevant
            
            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade'); // If customer is deleted, delete their ratings
            
            $table->foreign('assigned_to_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null'); // Keep rating, but remove link to deleted user
            
            $table->foreign('team_id')
                  ->references('id')
                  ->on('helpdesk_teams')
                  ->onDelete('set null'); // Keep rating, but remove link to deleted team
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