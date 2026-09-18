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
       // subjects table
Schema::create('subjects', function (Blueprint $table) {
    $table->id();
    $table->string('name');          // Quran, Fiqh, Seerah, Tawheed, Arabic
    $table->unsignedTinyInteger('capacity')->default(25);
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
