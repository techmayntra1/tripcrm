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
        $tables = [
            'companies',
            'users',
            'work_types',
            'work_leads',
            'cities',
            'expense_categories',
            'expense_types',
            'vendor_categories',
            'meeting_purposes',
            'lead_statuses',
            'project_statuses',
            'payment_modes',
            'gst_rates',
            'units',
            'update_types',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->softDeletes();
                });

               
                if (Schema::hasColumn($table, 'is_active')) {
                    DB::table($table)->where('is_active', false)->update(['deleted_at' => now()]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'companies',
            'users',
            'work_types',
            'work_leads',
            'cities',
            'expense_categories',
            'expense_types',
            'vendor_categories',
            'meeting_purposes',
            'lead_statuses',
            'project_statuses',
            'payment_modes',
            'gst_rates',
            'units',
            'update_types',
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'deleted_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropSoftDeletes();
                });
            }
        }
    }
};
