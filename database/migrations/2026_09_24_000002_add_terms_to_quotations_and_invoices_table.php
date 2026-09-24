<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->text('payment_terms')->nullable()->after('terms');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('terms')->nullable()->after('notes');
            $table->text('payment_terms')->nullable()->after('terms');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('payment_terms');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['terms', 'payment_terms']);
        });
    }
};
