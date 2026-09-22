<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_name', 100)->nullable()->after('email');
            $table->string('company_trn', 30)->nullable()->after('company_name');
            $table->string('country', 60)->nullable()->after('gst_number');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['company_name', 'company_trn', 'country']);
        });
    }
};
