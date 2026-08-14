<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->json('service_ids')->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('advance', 12, 2)->default(0);
            $table->foreignId('advance_bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('trip_service_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_service_id')->constrained()->cascadeOnDelete();
            $table->string('description', 255);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('trip_service_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('payment_date');
            $table->string('note', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_service_payments');
        Schema::dropIfExists('trip_service_addons');
        Schema::dropIfExists('trip_services');
    }
};
