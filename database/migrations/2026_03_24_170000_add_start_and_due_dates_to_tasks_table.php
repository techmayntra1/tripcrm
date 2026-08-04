<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('task_at', 'start_at');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->datetime('due_at')->nullable()->after('start_at');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn('due_at');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->renameColumn('start_at', 'task_at');
        });
    }
};
