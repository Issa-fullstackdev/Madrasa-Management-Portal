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
      // payments table
Schema::create('payments', function (Blueprint $table) {
    $table->id();
    $table->foreignId('student_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('amount')->default(10000);
    $table->string('month'); // e.g. "2026-09"
    $table->string('paybill_reference')->nullable();
    $table->enum('status', ['paid', 'pending'])->default('pending');
    $table->timestamp('paid_at')->nullable();
    $table->timestamps();
    $table->unique(['student_id', 'month']); // one fee record per student per month
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
