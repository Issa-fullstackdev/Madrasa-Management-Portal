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
        // students table
Schema::create('students', function (Blueprint $table) {
    $table->id();
    $table->string('admission_number')->unique();
    $table->string('first_name');
    $table->string('last_name');
    $table->date('date_of_birth')->nullable();
    $table->string('guardian_name');
    $table->string('guardian_phone');
    $table->enum('status', ['active', 'inactive'])->default('active');
    $table->date('enrolled_at');
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
