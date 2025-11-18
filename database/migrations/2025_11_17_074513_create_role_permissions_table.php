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
    Schema::create('role_permissions', function (Blueprint $table) {
        // Foreign key para sa roles table
        $table->unsignedBigInteger('role_id');
        $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');

        // Foreign key para sa permissions table
        $table->unsignedBigInteger('permission_id');
        $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');

        // Composite primary key
        $table->primary(['role_id', 'permission_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
