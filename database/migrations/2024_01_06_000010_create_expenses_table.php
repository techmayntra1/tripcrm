<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

       
        \DB::table('expense_categories')->insert([
            ['name' => 'Material', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Labour', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transport', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Utilities', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Office', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rent', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Maintenance', 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Salary', 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('expense_number', 20)->unique();
            $table->enum('expense_type', ['project', 'vendor', 'general', 'salary']);
            $table->date('expense_date');
            $table->enum('payment_mode', ['cash', 'bank_transfer', 'upi', 'cheque', 'card', 'credit']);
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('staff_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_number', 50)->nullable();
            $table->json('items')->nullable();
            $table->text('description')->nullable();
            $table->decimal('sub_total', 12, 2)->default(0);
            $table->integer('gst_percentage')->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('attachment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
