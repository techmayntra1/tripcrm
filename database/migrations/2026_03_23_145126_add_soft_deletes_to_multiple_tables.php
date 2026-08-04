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
       
        Schema::table('projects', function (Blueprint $table) {
            $table->softDeletes();
        });

       
        DB::table('projects')->where('is_active', false)->update(['deleted_at' => now()]);

       
        Schema::table('staff', function (Blueprint $table) {
            $table->softDeletes();
        });

       
        DB::table('staff')->where('is_active', false)->update(['deleted_at' => now()]);

       
        Schema::table('banks', function (Blueprint $table) {
            $table->softDeletes();
        });

       
        DB::table('banks')->where('is_active', false)->update(['deleted_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
