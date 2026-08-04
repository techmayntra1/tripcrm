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
        Schema::table('task_updates', function (Blueprint $table) {
            $table->dropColumn('follow_up_date');
        });

        Schema::table('meeting_updates', function (Blueprint $table) {
            $table->dropColumn('follow_up_date');
        });
    }

    public function down(): void
    {
        Schema::table('task_updates', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable();
        });

        Schema::table('meeting_updates', function (Blueprint $table) {
            $table->date('follow_up_date')->nullable();
        });
    }
};
