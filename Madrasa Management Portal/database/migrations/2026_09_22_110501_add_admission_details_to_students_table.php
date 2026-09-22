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
        Schema::table('students', function (Blueprint $table) {
            $table->string('middle_name')->nullable()->after('first_name');
            $table->enum('gender', ['male', 'female'])->nullable()->after('date_of_birth');
            $table->string('place_of_birth')->nullable()->after('gender');

            $table->string('father_first_name')->nullable()->after('place_of_birth');
            $table->string('father_middle_name')->nullable()->after('father_first_name');
            $table->string('father_last_name')->nullable()->after('father_middle_name');
            $table->string('father_phone')->nullable()->after('father_last_name');

            $table->string('mother_first_name')->nullable()->after('father_phone');
            $table->string('mother_middle_name')->nullable()->after('mother_first_name');
            $table->string('mother_last_name')->nullable()->after('mother_middle_name');
            $table->string('mother_phone')->nullable()->after('mother_last_name');

            $table->string('address_building')->nullable()->after('mother_phone');
            $table->string('address_road')->nullable()->after('address_building');
            $table->string('address_county')->nullable()->after('address_road');

            $table->string('emergency_contact_name')->nullable()->after('address_county');
            $table->string('emergency_contact_phone')->nullable()->after('emergency_contact_name');

            $table->string('guardian_signed_name')->nullable()->after('emergency_contact_phone');
            $table->timestamp('declaration_accepted_at')->nullable()->after('guardian_signed_name');

            $table->dropColumn(['guardian_name', 'guardian_phone']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();

            $table->dropColumn([
                'middle_name', 'gender', 'place_of_birth',
                'father_first_name', 'father_middle_name', 'father_last_name', 'father_phone',
                'mother_first_name', 'mother_middle_name', 'mother_last_name', 'mother_phone',
                'address_building', 'address_road', 'address_county',
                'emergency_contact_name', 'emergency_contact_phone',
                'guardian_signed_name', 'declaration_accepted_at',
            ]);
        });
    }
};
