<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->unsignedTinyInteger('interval_weeks')->default(1)->after('minutes_per_lesson');
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->unsignedTinyInteger('interval_weeks')->default(1)->after('minutes_per_lesson');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('interval_weeks');
        });

        Schema::table('cycles', function (Blueprint $table) {
            $table->dropColumn('interval_weeks');
        });
    }
};

