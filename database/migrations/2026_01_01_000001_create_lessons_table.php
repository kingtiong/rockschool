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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('scheduled_start_at');
            $table->dateTime('scheduled_end_at');
            $table->unsignedSmallInteger('minutes');

            // Basic statuses (UI uses color badges):
            // scheduled, completed, postponed, missed
            $table->string('status')->default('scheduled')->index();

            // Cycle progress label like 1/4, 2/4, ...
            $table->unsignedTinyInteger('sequence_in_cycle')->nullable();
            $table->unsignedTinyInteger('cycle_size')->default(4);

            $table->dateTime('absence_notified_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};

