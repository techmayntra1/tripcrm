<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
        $pendingStatus = DB::table('task_statuses')->where('name', 'Pending')->first();
        $defaultStatusId = $pendingStatus ? $pendingStatus->id : 1;

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('tasks', function (Blueprint $table) use ($defaultStatusId) {
            $table->foreignId('status_id')->default($defaultStatusId)->after('task_at')->constrained('task_statuses');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn('status_id');
            $table->string('status', 20)->default('pending')->after('task_at');
        });
    }
};
