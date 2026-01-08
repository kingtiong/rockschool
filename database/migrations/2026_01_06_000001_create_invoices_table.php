<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->foreignId('cycle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('base_amount_cents');
            $table->unsignedInteger('charges_amount_cents')->default(0);
            $table->unsignedInteger('total_amount_cents');
            $table->date('issue_date');
            $table->date('due_date');
            $table->string('status')->default('issued'); // issued, paid, void
            $table->timestamps();

            $table->unique(['cycle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

