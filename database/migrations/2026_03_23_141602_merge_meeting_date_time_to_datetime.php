<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       
        Schema::table('meetings', function (Blueprint $table) {
            $table->datetime('meeting_at')->nullable()->after('title');
        });

       
        DB::statement("UPDATE meetings SET meeting_at = CONCAT(meeting_date, ' ', COALESCE(meeting_time, '10:00:00'))");

       
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn(['meeting_date', 'meeting_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       
        Schema::table('meetings', function (Blueprint $table) {
            $table->date('meeting_date')->nullable()->after('title');
            $table->time('meeting_time')->nullable()->after('meeting_date');
        });

       
        DB::statement("UPDATE meetings SET meeting_date = DATE(meeting_at), meeting_time = TIME(meeting_at)");

       
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('meeting_at');
        });
    }
};
