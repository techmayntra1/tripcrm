<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Drops the retired ledger tables. All their data lives in income/expenses
 * after the preceding data migration; the bank balance is now derived purely
 * from Income (credits) and Expense (debits).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('project_service_payments');
        Schema::dropIfExists('bank_transactions');
    }

    public function down(): void
    {
        if (!Schema::hasTable('bank_transactions')) {
            Schema::create('bank_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('bank_id')->constrained()->onDelete('cascade');
                $table->enum('type', ['credit', 'debit']);
                $table->decimal('amount', 12, 2);
                $table->date('transaction_date');
                $table->string('description')->nullable();
                $table->string('reference_type', 50)->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->index(['reference_type', 'reference_id']);
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('project_service_payments')) {
            Schema::create('project_service_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_service_id')->constrained()->cascadeOnDelete();
                $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
                $table->decimal('amount', 12, 2);
                $table->date('payment_date');
                $table->string('note', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }
};
