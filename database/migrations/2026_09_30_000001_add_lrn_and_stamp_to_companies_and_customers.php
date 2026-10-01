<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('lrn_number', 30)->nullable()->after('vat_number');
            $table->string('stamp')->nullable()->after('logo');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->string('company_lrn', 30)->nullable()->after('company_trn');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['lrn_number', 'stamp']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('company_lrn');
        });
    }
};
