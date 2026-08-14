<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('trips', 'assigned_staff_ids')) {
            Schema::table('trips', function (Blueprint $table) {
                $table->json('assigned_staff_ids')->nullable()->after('assigned_staff_id');
            });
        }

       
        DB::table('trips')->whereNotNull('assigned_staff_id')->whereNull('assigned_staff_ids')->orderBy('id')->each(function ($trip) {
            DB::table('trips')->where('id', $trip->id)->update([
                'assigned_staff_ids' => json_encode([$trip->assigned_staff_id])
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn('assigned_staff_ids');
        });
    }
};
