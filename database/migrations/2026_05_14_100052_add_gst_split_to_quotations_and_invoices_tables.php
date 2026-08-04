<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->boolean('gst_split')->default(false)->after('gst_inclusive');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('gst_split')->default(false)->after('gst_inclusive');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('gst_split');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('gst_split');
        });
    }
};
