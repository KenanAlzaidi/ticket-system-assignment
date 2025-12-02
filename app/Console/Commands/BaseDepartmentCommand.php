<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

abstract class BaseDepartmentCommand extends Command
{
    /**
     * The connections array.
     *
     * @var array
     */
    protected array $connections;

    /**
     * Initialize the command and load connections.
     */
    public function __construct()
    {
        parent::__construct();

        $this->connections = array_filter(
            array_keys(config('database.connections')),
            fn($name) => str_ends_with($name, '_department')
        );
    }

    /**
     * The shared execution flow (Template Method Pattern).
     */
    public function handle(): int
    {
        $targetDb = $this->option('db');

        // 1. Validate Configuration
        if (empty($this->connections)) {
            $this->error('CRITICAL: No department connections found in config/database.php ending with "_department".');
            return Command::FAILURE;
        }

        // 2. Determine Target Scope
        $databasesToProcess = $this->connections;

        if ($targetDb) {
            if (!in_array($targetDb, $this->connections)) {
                $this->error("ERROR: The connection '{$targetDb}' is not a valid department connection.");
                $this->line("Available connections: " . implode(', ', $this->connections));
                return Command::FAILURE;
            }
            $databasesToProcess = [$targetDb];
        }

        // 3. Execution Loop
        foreach ($databasesToProcess as $connectionName) {
            $this->info("--- Processing schema for: {$connectionName} ---");

            try {
                // Check connection validity
                try {
                    DB::connection($connectionName)->getPdo();
                } catch (\Exception $e) {
                    $this->error("CONNECTION ERROR: Could not connect to '{$connectionName}'. Skipping...");
                    $this->error("Details: " . $e->getMessage());
                    continue;
                }

                // Delegate specific action to the child class
                $this->processConnection($connectionName);

                $this->info("SUCCESS: Operation completed for {$connectionName}.");

            } catch (\Exception $e) {
                $this->error("OPERATION FAILED for {$connectionName}.");
                $this->error("Error: " . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Abstract method to be implemented by child classes (Migrate vs Rollback).
     */
    abstract protected function processConnection(string $connectionName): void;
}

