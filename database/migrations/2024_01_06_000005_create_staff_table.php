<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('mobile', 15);
            $table->string('email', 100)->nullable();
            $table->enum('role', [
                'supervisor',
                'site_manager',
                'designer',
                'carpenter',
                'electrician',
                'plumber',
                'painter',
                'helper',
                'driver',
                'office_staff'
            ]);
            $table->date('joining_date');
            $table->string('aadhar_number', 12)->nullable();
            $table->string('pan_number', 10)->nullable();
            $table->text('address')->nullable();
            $table->enum('salary_type', ['monthly', 'daily', 'hourly'])->default('monthly');
            $table->decimal('salary_amount', 12, 2);
            $table->decimal('overtime_rate', 12, 2)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('account_number', 20)->nullable();
            $table->string('ifsc_code', 11)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
