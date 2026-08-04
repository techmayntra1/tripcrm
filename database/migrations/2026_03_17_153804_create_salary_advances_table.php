<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salary_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->decimal('remaining_amount', 12, 2);
            $table->date('advance_date');
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque'])->default('cash');
            $table->foreignId('bank_id')->nullable()->constrained()->onDelete('set null');
            $table->string('reference_number', 50)->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'active', 'completed'])->default('active');
            $table->timestamps();
        });

        Schema::create('salary_advance_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_advance_id')->constrained()->onDelete('cascade');
            $table->foreignId('expense_id')->constrained()->onDelete('cascade');
            $table->decimal('deduction_amount', 12, 2);
            $table->date('deduction_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_advance_deductions');
        Schema::dropIfExists('salary_advances');
    }
};
