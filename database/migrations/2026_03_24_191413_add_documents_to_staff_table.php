<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->string('pan_card')->nullable()->after('ifsc_code');
            $table->string('aadhar_front')->nullable()->after('pan_card');
            $table->string('aadhar_back')->nullable()->after('aadhar_front');
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn(['pan_card', 'aadhar_front', 'aadhar_back']);
        });
    }
};
