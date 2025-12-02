<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;

class MigrateDepartments extends BaseDepartmentCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:departments
                            {--db= : Optional specific database connection name (e.g., technical_issues_department) to migrate}
                            {--force-migration : Force the migration to run in production or non-interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrates the tickets and admin_notes table schemas to all department databases or a specific one.';

    /**
     * Implement the specific logic for migration.
     */
    protected function processConnection(string $connectionName): void
    {
        // Run the tickets table migration
        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/departments/2025_11_28_171632_create_tickets_table.php',
            '--force' => $this->option('force-migration'),
        ], $this->output);

        // Run the admin_notes table migration
        Artisan::call('migrate', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/departments/2025_12_01_112010_create_admin_notes_table.php',
            '--force' => $this->option('force-migration'),
        ], $this->output);
    }
}
