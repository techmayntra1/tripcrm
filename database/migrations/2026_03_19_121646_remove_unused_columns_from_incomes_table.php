<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn(['tds_amount', 'net_amount', 'transaction_id', 'bank_reference']);
        });
    }

    public function down(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->decimal('tds_amount', 12, 2)->default(0)->after('amount');
            $table->decimal('net_amount', 12, 2)->after('tds_amount');
            $table->string('transaction_id', 50)->nullable()->after('bank_name');
            $table->string('bank_reference', 50)->nullable()->after('transaction_id');
        });
    }
};
