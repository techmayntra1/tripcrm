<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

       
        DB::table('vendor_categories')->insert([
            ['name' => 'Tiles & Flooring', 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Plywood & Hardware', 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Electrical', 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Paint & Finishing', 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Granite & Marble', 'sort_order' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Glass & Aluminium', 'sort_order' => 6, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sanitary & Plumbing', 'sort_order' => 7, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Labour Contractor', 'sort_order' => 8, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transportation', 'sort_order' => 9, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Other', 'sort_order' => 10, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_categories');
    }
};
