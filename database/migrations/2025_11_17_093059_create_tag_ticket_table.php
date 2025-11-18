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
        Schema::create('tag_ticket', function (Blueprint $table) {
            // Foreign key para sa 'tags' table
            $table->foreignId('tag_id')->constrained()->onDelete('cascade');
            
            // Foreign key para sa 'tickets' table
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');

            // Siguraduhin na ang isang tag ay hindi pwedeng ilagay
            // nang paulit-ulit sa iisang ticket
            $table->primary(['tag_id', 'ticket_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tag_ticket');
    }
};