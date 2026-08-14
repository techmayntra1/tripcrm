<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number', 20)->unique();
            $table->string('name', 200);
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->json('work_type')->nullable();
            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->enum('status', ['planning', 'in_progress', 'on_hold', 'completed', 'cancelled'])->default('planning');
            $table->text('site_address')->nullable();
            $table->text('description')->nullable();
            $table->decimal('budget', 12, 2)->default(0);
            $table->decimal('advance_received', 12, 2)->default(0);
            $table->foreignId('assigned_staff_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

       
        Schema::table('trip_addons', function (Blueprint $table) {
            $table->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('trip_addons', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
        });
        Schema::dropIfExists('trips');
    }
};
