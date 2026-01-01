<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->foreign('fee_plan_id')->references('id')->on('fee_plans')->restrictOnDelete();
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->foreign('enrollment_id')->references('id')->on('enrollments')->cascadeOnDelete();
        });

        Schema::table('payments', function (Blueprint $table) {
            // Ensure one payment per cycle (simplifies workflow)
            $table->unique('cycle_id');
        });

        Schema::table('payment_attachments', function (Blueprint $table) {
            $table->foreign('payment_id')->references('id')->on('payments')->cascadeOnDelete();
        });

        Schema::table('teacher_earnings', function (Blueprint $table) {
            $table->foreign('teacher_payout_id')->references('id')->on('teacher_payouts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('teacher_earnings', function (Blueprint $table) {
            $table->dropForeign(['teacher_payout_id']);
        });

        Schema::table('payment_attachments', function (Blueprint $table) {
            $table->dropForeign(['payment_id']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['cycle_id']);
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->dropForeign(['enrollment_id']);
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropForeign(['fee_plan_id']);
        });
    }
};

