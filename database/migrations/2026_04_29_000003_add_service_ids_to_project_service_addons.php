<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_service_addons', function (Blueprint $table) {
            $table->json('service_ids')->nullable()->after('project_service_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_service_addons', function (Blueprint $table) {
            $table->dropColumn('service_ids');
        });
    }
};
