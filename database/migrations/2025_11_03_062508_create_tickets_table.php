<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->text('description');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('team_id')->nullable();
            $table->unsignedBigInteger('assigned_to_employee_id')->nullable();
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->enum('stage', ['Open', 'In Progress', 'Pending Customer', 'Resolved', 'Closed'])->default('Open');
            $table->dateTime('deadline')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')
                  ->references('id')
                  ->on('customers')
                  ->onDelete('cascade');

            $table->foreign('team_id')
                  ->references('id')
                  ->on('helpdesk_teams')
                  ->onDelete('set null');

            $table->foreign('assigned_to_employee_id')
                ->references('id')
                ->on('employees')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};