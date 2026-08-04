<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
       
       
        DB::statement("UPDATE incomes SET description = notes WHERE (description IS NULL OR description = '') AND notes IS NOT NULL AND notes != ''");

       
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

       
       
        DB::statement("UPDATE expenses SET notes = CONCAT_WS(' | ',
            NULLIF(reference_number, ''),
            NULLIF(description, ''),
            NULLIF(notes, '')
        ) WHERE (reference_number IS NOT NULL AND reference_number != '') OR (description IS NOT NULL AND description != '')");

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn(['reference_number', 'description']);
        });

       
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('notes', 'description');
        });
    }

    public function down(): void
    {
       
        Schema::table('expenses', function (Blueprint $table) {
            $table->renameColumn('description', 'notes');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('reference_number', 50)->nullable()->after('bank_id');
            $table->string('description', 255)->nullable()->after('items');
        });

       
        Schema::table('incomes', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('bank_name');
        });
    }
};
