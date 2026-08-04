<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->decimal('paid_amount', 12, 2)->default(0)->after('grand_total');
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid')->after('paid_amount');
        });

       
        DB::table('expenses')->update([
            'paid_amount' => DB::raw('grand_total'),
            'payment_status' => 'paid'
        ]);
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'payment_status']);
        });
    }
};
