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
        Schema::create('cycles', function (Blueprint $table) {
            $table->id();
            // NOTE: enrollments table is created in the same timestamp batch; add FK in a later migration.
            $table->unsignedBigInteger('enrollment_id')->index();

            // Snapshot values (so future fee plan changes don't affect past cycles)
            $table->unsignedInteger('cycle_fee_cents');
            $table->unsignedTinyInteger('lessons_per_cycle')->default(4);
            $table->unsignedSmallInteger('minutes_per_lesson');
            $table->unsignedSmallInteger('cycle_minutes_total');

            $table->unsignedInteger('cycle_number')->default(1);
            $table->string('status')->default('awaiting_student_payment')->index();
            // awaiting_student_payment, payment_submitted, paid, active, completed

            $table->date('starts_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycles');
    }
};
