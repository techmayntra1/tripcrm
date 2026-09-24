<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Shared master for "Terms & Conditions" and "Payment Terms" (distinguished by type)
        Schema::create('term_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type', 20)->index(); // terms | payment
            $table->string('name', 100);
            $table->text('content');
            $table->boolean('is_default')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('term_templates');
    }
};
