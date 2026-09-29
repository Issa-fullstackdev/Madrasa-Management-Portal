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
        Schema::table('student_subject', function (Blueprint $table) {
            $table->unsignedTinyInteger('juz_completed')->default(0)->after('subject_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_subject', function (Blueprint $table) {
            $table->dropColumn('juz_completed');
        });
    }
};
