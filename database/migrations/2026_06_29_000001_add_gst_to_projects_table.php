<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('gst_percent', 5, 2)->default(0)->after('budget');
            $table->boolean('gst_inclusive')->default(false)->after('gst_percent');
            $table->boolean('gst_split')->default(false)->after('gst_inclusive');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_split');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['gst_percent', 'gst_inclusive', 'gst_split', 'gst_amount']);
        });
    }
};
