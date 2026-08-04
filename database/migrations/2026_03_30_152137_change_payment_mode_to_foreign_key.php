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
            $table->foreignId('payment_mode_id')->nullable()->after('payment_mode')->constrained('payment_modes')->nullOnDelete();
        });

       
        DB::statement("
            UPDATE expenses e
            JOIN payment_modes pm ON pm.slug = e.payment_mode
            SET e.payment_mode_id = pm.id
            WHERE e.payment_mode IS NOT NULL
        ");

       
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });

       
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('payment_mode_id')->nullable()->after('payment_mode')->constrained('payment_modes')->nullOnDelete();
        });

       
        DB::statement("
            UPDATE incomes i
            JOIN payment_modes pm ON pm.slug = i.payment_mode
            SET i.payment_mode_id = pm.id
            WHERE i.payment_mode IS NOT NULL
        ");

       
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });

       
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->foreignId('payment_mode_id')->nullable()->after('payment_mode')->constrained('payment_modes')->nullOnDelete();
        });

       
        DB::statement("
            UPDATE salary_advances sa
            JOIN payment_modes pm ON pm.slug = sa.payment_mode
            SET sa.payment_mode_id = pm.id
            WHERE sa.payment_mode IS NOT NULL
        ");

       
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }

    public function down(): void
    {
       
        Schema::table('expenses', function (Blueprint $table) {
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque', 'card', 'credit'])->nullable()->after('payment_mode_id');
        });

        DB::statement("
            UPDATE expenses e
            JOIN payment_modes pm ON pm.id = e.payment_mode_id
            SET e.payment_mode = pm.slug
            WHERE e.payment_mode_id IS NOT NULL
        ");

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['payment_mode_id']);
            $table->dropColumn('payment_mode_id');
        });

       
        Schema::table('incomes', function (Blueprint $table) {
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque', 'card'])->nullable()->after('payment_mode_id');
        });

        DB::statement("
            UPDATE incomes i
            JOIN payment_modes pm ON pm.id = i.payment_mode_id
            SET i.payment_mode = pm.slug
            WHERE i.payment_mode_id IS NOT NULL
        ");

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropForeign(['payment_mode_id']);
            $table->dropColumn('payment_mode_id');
        });

       
        Schema::table('salary_advances', function (Blueprint $table) {
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque'])->default('cash')->after('payment_mode_id');
        });

        DB::statement("
            UPDATE salary_advances sa
            JOIN payment_modes pm ON pm.id = sa.payment_mode_id
            SET sa.payment_mode = pm.slug
            WHERE sa.payment_mode_id IS NOT NULL
        ");

        Schema::table('salary_advances', function (Blueprint $table) {
            $table->dropForeign(['payment_mode_id']);
            $table->dropColumn('payment_mode_id');
        });
    }
};
