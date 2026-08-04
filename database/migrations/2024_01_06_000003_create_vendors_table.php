<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->foreignId('category_id')->constrained('vendor_categories')->cascadeOnDelete();
            $table->string('contact_person', 100)->nullable();
            $table->string('mobile', 15);
            $table->string('email', 100)->nullable();
            $table->string('gst_number', 15)->nullable();
            $table->string('pan_number', 10)->nullable();
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->text('address')->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendors');
    }
};
