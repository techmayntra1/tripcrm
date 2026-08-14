<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Renames the "project" domain to "trip" at the database level.
 *
 * Guarded so it is a no-op on fresh installs: the (rewritten) create
 * migrations already produce trip-named tables, so `projects` will not
 * exist and up() returns early. On existing databases it renames the
 * physical schema in place, preserving all data.
 */
return new class extends Migration
{
    /**
     * Tables to rename: old => new.
     */
    private array $tables = [
        'projects'               => 'trips',
        'project_addons'         => 'trip_addons',
        'project_files'          => 'trip_files',
        'project_services'       => 'trip_services',
        'project_service_addons' => 'trip_service_addons',
        'project_statuses'       => 'trip_statuses',
    ];

    public function up(): void
    {
        // Fresh install already trip-named -> nothing to do.
        if (! Schema::hasTable('projects')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        // 1. Drop foreign keys that reference the project columns/tables.
        foreach (['expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks', 'project_addons', 'project_files', 'project_services'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropForeign(['project_id']));
        }
        Schema::table('project_service_addons', fn (Blueprint $table) => $table->dropForeign(['project_service_id']));

        // 2. Rename tables.
        foreach ($this->tables as $old => $new) {
            if (Schema::hasTable($old) && ! Schema::hasTable($new)) {
                Schema::rename($old, $new);
            }
        }

        // 3. Rename columns (on the now trip-named tables).
        $this->renameColumn('trips', 'project_number', 'trip_number');
        foreach (['trip_addons', 'trip_files', 'trip_services', 'expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks'] as $t) {
            $this->renameColumn($t, 'project_id', 'trip_id');
        }
        $this->renameColumn('trip_service_addons', 'project_service_id', 'trip_service_id');
        $this->renameColumn('expenses', 'project_service_id', 'trip_service_id');

        // 4. Re-add foreign keys with the new column/table names.
        Schema::table('trip_addons', fn (Blueprint $t) => $t->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete());
        Schema::table('trip_files', fn (Blueprint $t) => $t->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete());
        Schema::table('trip_services', fn (Blueprint $t) => $t->foreign('trip_id')->references('id')->on('trips')->cascadeOnDelete());
        Schema::table('trip_service_addons', fn (Blueprint $t) => $t->foreign('trip_service_id')->references('id')->on('trip_services')->cascadeOnDelete());
        foreach (['expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->foreign('trip_id')->references('id')->on('trips')->nullOnDelete());
        }

        Schema::enableForeignKeyConstraints();

        // 5. Convert enum values 'project' -> 'trip'.
        DB::statement("ALTER TABLE expenses MODIFY expense_type ENUM('project','trip','vendor','general','salary','service') NOT NULL");
        DB::table('expenses')->where('expense_type', 'project')->update(['expense_type' => 'trip']);
        DB::statement("ALTER TABLE expenses MODIFY expense_type ENUM('trip','vendor','general','salary','service') NOT NULL");

        DB::statement("ALTER TABLE incomes MODIFY income_type ENUM('project','trip','advance','other') NOT NULL");
        DB::table('incomes')->where('income_type', 'project')->update(['income_type' => 'trip']);
        DB::statement("ALTER TABLE incomes MODIFY income_type ENUM('trip','advance','other') NOT NULL");

        // 6. Permission module key + any seeded label data.
        DB::table('permissions')->where('module', 'projects')->update(['module' => 'trips']);
        DB::table('trips')->where('work_type', 'Turnkey Project')->update(['work_type' => 'Turnkey Trip']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('trips')) {
            return;
        }

        Schema::disableForeignKeyConstraints();

        foreach (['expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks', 'trip_addons', 'trip_files', 'trip_services'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->dropForeign(['trip_id']));
        }
        Schema::table('trip_service_addons', fn (Blueprint $table) => $table->dropForeign(['trip_service_id']));

        foreach (array_reverse($this->tables, true) as $old => $new) {
            if (Schema::hasTable($new) && ! Schema::hasTable($old)) {
                Schema::rename($new, $old);
            }
        }

        $this->renameColumn('projects', 'trip_number', 'project_number');
        foreach (['project_addons', 'project_files', 'project_services', 'expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks'] as $t) {
            $this->renameColumn($t, 'trip_id', 'project_id');
        }
        $this->renameColumn('project_service_addons', 'trip_service_id', 'project_service_id');
        $this->renameColumn('expenses', 'trip_service_id', 'project_service_id');

        Schema::table('project_addons', fn (Blueprint $t) => $t->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete());
        Schema::table('project_files', fn (Blueprint $t) => $t->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete());
        Schema::table('project_services', fn (Blueprint $t) => $t->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete());
        Schema::table('project_service_addons', fn (Blueprint $t) => $t->foreign('project_service_id')->references('id')->on('project_services')->cascadeOnDelete());
        foreach (['expenses', 'incomes', 'invoices', 'meetings', 'quotations', 'tasks'] as $t) {
            Schema::table($t, fn (Blueprint $table) => $table->foreign('project_id')->references('id')->on('projects')->nullOnDelete());
        }

        Schema::enableForeignKeyConstraints();

        DB::statement("ALTER TABLE expenses MODIFY expense_type ENUM('project','trip','vendor','general','salary','service') NOT NULL");
        DB::table('expenses')->where('expense_type', 'trip')->update(['expense_type' => 'project']);
        DB::statement("ALTER TABLE expenses MODIFY expense_type ENUM('project','vendor','general','salary','service') NOT NULL");

        DB::statement("ALTER TABLE incomes MODIFY income_type ENUM('project','trip','advance','other') NOT NULL");
        DB::table('incomes')->where('income_type', 'trip')->update(['income_type' => 'project']);
        DB::statement("ALTER TABLE incomes MODIFY income_type ENUM('project','advance','other') NOT NULL");

        DB::table('permissions')->where('module', 'trips')->update(['module' => 'projects']);
        DB::table('projects')->where('work_type', 'Turnkey Trip')->update(['work_type' => 'Turnkey Project']);
    }

    private function renameColumn(string $table, string $from, string $to): void
    {
        if (Schema::hasColumn($table, $from) && ! Schema::hasColumn($table, $to)) {
            Schema::table($table, fn (Blueprint $t) => $t->renameColumn($from, $to));
        }
    }
};
