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
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('gst_inclusive')->default(false)->after('gst_percent');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('gst_inclusive')->default(false)->after('gst_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('gst_inclusive');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('gst_inclusive');
        });
    }
};
