<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UAE quotations charge VAT on the per-line service fee only (same as invoices).
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('service_fee', 12, 2)->default(0)->after('discount');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('service_fee');
        });
    }
};
