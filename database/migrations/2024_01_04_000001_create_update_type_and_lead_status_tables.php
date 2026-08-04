<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('icon', 50)->nullable();
            $table->string('color', 20)->default('primary');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('color', 20)->default('secondary');
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

       
        DB::table('update_types')->insert([
            ['name' => 'Phone Call', 'icon' => 'bi-telephone', 'color' => 'primary', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Email Sent', 'icon' => 'bi-envelope', 'color' => 'secondary', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'WhatsApp Message', 'icon' => 'bi-whatsapp', 'color' => 'success', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Meeting Update', 'icon' => 'bi-calendar-event', 'color' => 'info', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Status Change', 'icon' => 'bi-arrow-repeat', 'color' => 'warning', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'General Note', 'icon' => 'bi-sticky', 'color' => 'dark', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);

       
        DB::table('lead_statuses')->insert([
            ['name' => 'New', 'color' => 'warning', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Contacted', 'color' => 'info', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Qualified', 'color' => 'primary', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Negotiation', 'color' => 'secondary', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Won', 'color' => 'success', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lost', 'color' => 'danger', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
        Schema::dropIfExists('update_types');
    }
};
