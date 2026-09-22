<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Region drives the tax fields (GST/PAN vs VAT) and the currency (INR vs AED).
        // Existing rows default to India so nothing changes for current data.
        Schema::table('companies', function (Blueprint $table) {
            $table->enum('country', ['india', 'uae'])->default('india')->after('contact_person');
        });

        Schema::table('banks', function (Blueprint $table) {
            $table->enum('country', ['india', 'uae'])->default('india')->after('bank_name');
            $table->string('iban', 34)->nullable()->after('ifsc_code');
        });
    }

    public function down(): void
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['country', 'iban']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
};
