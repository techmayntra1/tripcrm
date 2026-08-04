<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('projects', 'assigned_staff_ids')) {
            Schema::table('projects', function (Blueprint $table) {
                $table->json('assigned_staff_ids')->nullable()->after('assigned_staff_id');
            });
        }

       
        DB::table('projects')->whereNotNull('assigned_staff_id')->whereNull('assigned_staff_ids')->orderBy('id')->each(function ($project) {
            DB::table('projects')->where('id', $project->id)->update([
                'assigned_staff_ids' => json_encode([$project->assigned_staff_id])
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('assigned_staff_ids');
        });
    }
};
