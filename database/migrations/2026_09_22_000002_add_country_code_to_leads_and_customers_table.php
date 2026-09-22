<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dialling code stored separately from the mobile number (e.g. +91, +971).
        // Existing rows default to India.
        Schema::table('leads', function (Blueprint $table) {
            $table->string('country_code', 6)->default('+91')->after('name');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('country_code', 6)->default('+91')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('country_code');
        });
    }
};
