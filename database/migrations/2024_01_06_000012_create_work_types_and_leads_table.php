<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
        Schema::create('work_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('work_types')->insert([
            ['name' => 'Interior Design', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Modular Kitchen', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Wardrobe', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'False Ceiling', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Renovation', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Turnkey Trip', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);

       
        Schema::create('work_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('work_leads')->insert([
            ['name' => 'Website', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Referral', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Social Media', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Walk-in', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Advertisement', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Google Ads', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Facebook', 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Instagram', 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
        ]);

       
        Schema::create('meeting_purposes', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('meeting_purposes')->insert([
            ['name' => 'Site Visit', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Design Discussion', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Quotation Review', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Trip Update', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Payment Collection', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Final Handover', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_purposes');
        Schema::dropIfExists('work_leads');
        Schema::dropIfExists('work_types');
    }
};
