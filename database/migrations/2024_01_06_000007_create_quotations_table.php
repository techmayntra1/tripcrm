<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->string('quotation_number', 20)->unique();
            $table->date('date');
            $table->date('valid_until')->nullable();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 200)->nullable();
            $table->enum('quotation_type', ['pdf', 'items'])->default('items');
            $table->string('quotation_pdf')->nullable();
            $table->text('pdf_description')->nullable();
            $table->json('items')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->integer('gst_percent')->default(0);
            $table->decimal('gst', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->text('terms')->nullable();
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired'])->default('draft');
            $table->timestamps();
        });

       
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('advance_received')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('quotation_id');
        });
        Schema::dropIfExists('quotations');
    }
};
