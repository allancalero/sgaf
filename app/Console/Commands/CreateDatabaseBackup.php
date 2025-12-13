<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateDatabaseBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create {--type=automatic : Type of backup (manual or automatic)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a database backup';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating database backup...');

        try {
            $backupService = app(\App\Services\BackupService::class);
            $type = $this->option('type');

            $backup = $backupService->createBackup($type);

            $this->info("Backup created successfully: {$backup->filename}");
            $this->info("Size: {$backup->human_size}");

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Backup failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
