<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // UAE invoices charge VAT on the service fee only; agent commission is tracked
        // for records and is never part of the grand total.
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('service_fee', 12, 2)->default(0)->after('discount');
            $table->decimal('vat_percent', 5, 2)->nullable()->after('service_fee');
            $table->foreignId('agent_id')->nullable()->after('quotation_id')->constrained('agents')->nullOnDelete();
            $table->decimal('agent_commission', 12, 2)->default(0)->after('grand_total');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('agent_id');
            $table->dropColumn(['service_fee', 'vat_percent', 'agent_commission']);
        });
    }
};
