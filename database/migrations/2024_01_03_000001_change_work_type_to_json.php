<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
       
        DB::table('leads')->whereNotNull('work_type')->get()->each(function ($lead) {
            DB::table('leads')->where('id', $lead->id)->update([
                'work_type' => json_encode([$lead->work_type])
            ]);
        });

       
        DB::table('customers')->whereNotNull('work_type')->get()->each(function ($customer) {
            DB::table('customers')->where('id', $customer->id)->update([
                'work_type' => json_encode([$customer->work_type])
            ]);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->json('work_type')->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->json('work_type')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->string('work_type')->nullable()->change();
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('work_type')->nullable()->change();
        });

       
        DB::table('leads')->whereNotNull('work_type')->get()->each(function ($lead) {
            $workTypes = json_decode($lead->work_type, true);
            DB::table('leads')->where('id', $lead->id)->update([
                'work_type' => is_array($workTypes) ? ($workTypes[0] ?? null) : null
            ]);
        });

       
        DB::table('customers')->whereNotNull('work_type')->get()->each(function ($customer) {
            $workTypes = json_decode($customer->work_type, true);
            DB::table('customers')->where('id', $customer->id)->update([
                'work_type' => is_array($workTypes) ? ($workTypes[0] ?? null) : null
            ]);
        });
    }
};
