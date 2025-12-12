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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable()->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->string('email', 255)->unique();
            $table->string('phone_number', 20)->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->string('employee_code', 20)->unique()->nullable();
            $table->date('hire_date')->nullable();
            
            $table->timestamps();

            // --- Foreign Keys ---
            $table->foreignId('company_id')->nullable()->constrained('companies')->onDelete('set null');
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
            $table->foreign('department_id')
                  ->references('id')
                  ->on('departments')
                  ->onDelete('set null');
            $table->foreign('position_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('set null');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
