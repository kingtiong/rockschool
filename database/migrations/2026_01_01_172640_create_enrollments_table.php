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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            // NOTE: fee_plans table is created in the same timestamp batch; add FK in a later migration.
            $table->unsignedBigInteger('fee_plan_id')->index();

            // For 1-hour plans, student may choose 30 mins (half fee).
            // For Grade 7–8 plans, minutes_per_lesson is 45.
            $table->unsignedSmallInteger('minutes_per_lesson');

            $table->string('status')->default('active')->index(); // active/inactive
            $table->date('started_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
