<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Artisan;

class RollbackDepartments extends BaseDepartmentCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:rollback-departments
                            {--db= : Optional specific database connection name (e.g., technical_issues_department) to rollback}
                            {--force-rollback : Force the rollback to run in production or non-interactive mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback the tickets and admin_notes table schemas from all department databases or a specific one.';

    /**
     * Implement the specific logic for rollback.
     */
    protected function processConnection(string $connectionName): void
    {
        // Rollback admin_notes first (Child table with Foreign Key)
        Artisan::call('migrate:rollback', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/departments/2025_12_01_112010_create_admin_notes_table.php',
            '--force' => $this->option('force-rollback'),
        ], $this->output);

        // Rollback tickets table
        Artisan::call('migrate:rollback', [
            '--database' => $connectionName,
            '--path' => 'database/migrations/departments/2025_11_28_171632_create_tickets_table.php',
            '--force' => $this->option('force-rollback'),
        ], $this->output);
    }
}
