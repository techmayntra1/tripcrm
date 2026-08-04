<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE expenses MODIFY COLUMN expense_type ENUM('project', 'vendor', 'general', 'salary', 'service') NOT NULL");
    }

    public function down(): void
    {
        // Reclassify any service expenses so the value fits the old enum before shrinking it.
        DB::table('expenses')->where('expense_type', 'service')->update(['expense_type' => 'general']);
        DB::statement("ALTER TABLE expenses MODIFY COLUMN expense_type ENUM('project', 'vendor', 'general', 'salary') NOT NULL");
    }
};
