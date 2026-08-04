<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->softDeletes();
            $table->json('categories')->nullable()->after('category_id');
        });

       
        DB::table('vendors')->whereNotNull('category_id')->get()->each(function ($vendor) {
            DB::table('vendors')
                ->where('id', $vendor->id)
                ->update(['categories' => json_encode([$vendor->category_id])]);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->dropColumn('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained('vendor_categories');
            $table->boolean('is_active')->default(true);
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('categories');
        });
    }
};
