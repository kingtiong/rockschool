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
        Schema::create('teacher_earnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained('lessons')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            // NOTE: teacher_payouts table is created later; add FK in a later migration.
            $table->unsignedBigInteger('teacher_payout_id')->nullable()->index();

            $table->unsignedInteger('amount_cents');
            $table->string('status')->default('unpaid')->index(); // unpaid/paid
            $table->dateTime('calculated_at')->nullable();
            $table->timestamps();

            $table->unique('lesson_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_earnings');
    }
};
