<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Moves fees from "one payment row per student per month" to a ledger:
 * fee_charges (what is owed each month) and payments (money actually received).
 * The balance is charges minus payments, so arrears and prepayments fall out naturally.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->unsignedInteger('monthly_fee')->default(10000)->after('status');
        });

        Schema::create('fee_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('month', 7); // e.g. "2026-09"
            $table->unsignedInteger('amount');
            $table->timestamps();
            $table->unique(['student_id', 'month']);
        });

        Schema::create('non_billable_months', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7)->unique();
            $table->string('reason')->nullable();
            $table->timestamps();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('method')->default('mpesa')->after('amount');
            $table->string('mpesa_code', 10)->nullable()->after('method');
            $table->string('verification_status')->nullable()->after('mpesa_code');
            $table->timestamp('verified_at')->nullable()->after('verification_status');
            $table->string('notes')->nullable()->after('verified_at');
            $table->foreignId('recorded_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
        });

        // Carry over existing records. "pending" rows never represented money received.
        DB::table('payments')->where('status', '!=', 'paid')->delete();

        $seenCodes = [];
        DB::table('payments')->orderBy('id')->get()->each(function ($payment) use (&$seenCodes) {
            $code = strtoupper(preg_replace('/\s+/', '', (string) $payment->paybill_reference));
            $notes = null;

            if ($code === '') {
                $code = null;
                $notes = 'Recorded before M-Pesa code checks; no code entered.';
            } elseif (isset($seenCodes[$code]) || strlen($code) > 10) {
                $notes = "Original reference \"{$payment->paybill_reference}\" was a duplicate or invalid code.";
                $code = null;
            } else {
                $seenCodes[$code] = true;
            }

            DB::table('payments')->where('id', $payment->id)->update([
                'mpesa_code' => $code,
                'verification_status' => 'unverified',
                'notes' => $notes,
                'paid_at' => $payment->paid_at ?? $payment->updated_at ?? now(),
            ]);
        });

        $hasStudentIndex = Schema::hasIndex('payments', ['student_id']);
        $hasMonthUnique = Schema::hasIndex('payments', ['student_id', 'month'], 'unique');
        Schema::table('payments', function (Blueprint $table) use ($hasStudentIndex, $hasMonthUnique) {
            // Keep an index on student_id for the foreign key before dropping the composite unique.
            if (! $hasStudentIndex) {
                $table->index('student_id');
            }
            if ($hasMonthUnique) {
                $table->dropUnique(['student_id', 'month']);
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['month', 'status', 'paybill_reference']);
            $table->unique('mpesa_code');
        });
    }

    public function down(): void
    {
        // The student_id index is left in place for the foreign key. The old
        // one-row-per-month unique rule is not restored, because split payments may break it.
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['mpesa_code']);
            $table->string('month')->nullable();
            $table->string('paybill_reference')->nullable();
            $table->enum('status', ['paid', 'pending'])->default('paid');
        });

        DB::table('payments')->update([
            'paybill_reference' => DB::raw('mpesa_code'),
            'month' => DB::raw("substr(paid_at, 1, 7)"),
        ]);

        Schema::table('payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('recorded_by');
            $table->dropColumn(['method', 'mpesa_code', 'verification_status', 'verified_at', 'notes']);
        });

        Schema::dropIfExists('non_billable_months');
        Schema::dropIfExists('fee_charges');

        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('monthly_fee');
        });
    }
};
