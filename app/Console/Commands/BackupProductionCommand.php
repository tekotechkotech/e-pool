<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class BackupProductionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup {--dry-run : Simulate backup process without creating files or deleting old backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform automated production backup of MariaDB database and bukti setor storage with 14-day rotation';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $this->info($dryRun ? '--- Starting e-pool Backup (DRY RUN) ---' : '--- Starting e-pool Backup ---');

        $backupDir = '/home/faizen/backups/e-pool';
        $timestamp = Carbon::now()->format('Ymd_His');

        if (!File::exists($backupDir)) {
            if ($dryRun) {
                $this->info("[Dry-Run] Would create directory: {$backupDir}");
            } else {
                File::makeDirectory($backupDir, 0755, true);
                $this->info("Created backup directory: {$backupDir}");
            }
        }

        $dbFile = "{$backupDir}/e_pool_db_{$timestamp}.sql.gz";
        $buktiFile = "{$backupDir}/e_pool_bukti_setor_{$timestamp}.tar.gz";

        // 1. Backup MariaDB Database
        $this->info("1. Backing up MariaDB database...");
        $dbCommand = 'set -o pipefail; docker exec mariadb mariadb-dump -u faizen -ppasswordku e-pool | gzip -c > ' . escapeshellarg($dbFile);

        if ($dryRun) {
            $this->info("[Dry-Run] Target database archive: {$dbFile}");
            $this->line("  Command: {$dbCommand}");
        } else {
            $process = Process::timeout(300)->run('bash -c ' . escapeshellarg($dbCommand));
            if ($process->failed()) {
                $this->error("Database backup failed: " . $process->errorOutput());
                return Command::FAILURE;
            }
            if (!File::exists($dbFile) || File::size($dbFile) === 0) {
                $this->error("Database backup file is missing or empty: {$dbFile}");
                return Command::FAILURE;
            }
            $dbSize = round(File::size($dbFile) / 1024, 2);
            $this->info("Database backup created successfully: {$dbFile} ({$dbSize} KB)");
        }

        // 2. Backup Bukti Setor
        $this->info("2. Backing up bukti setor files...");
        $storagePublicDir = storage_path('app/public');
        $buktiDir = "{$storagePublicDir}/bukti_setor";

        if (!File::exists($buktiDir)) {
            File::makeDirectory($buktiDir, 0755, true);
        }

        $tarCommand = 'tar -czf ' . escapeshellarg($buktiFile) . ' -C ' . escapeshellarg($storagePublicDir) . ' bukti_setor';

        if ($dryRun) {
            $this->info("[Dry-Run] Target bukti setor archive: {$buktiFile}");
            $this->line("  Command: {$tarCommand}");
        } else {
            $process = Process::timeout(300)->run($tarCommand);
            if ($process->failed()) {
                $this->error("Bukti setor backup failed: " . $process->errorOutput());
                return Command::FAILURE;
            }
            if (!File::exists($buktiFile)) {
                $this->error("Bukti setor archive file was not created: {$buktiFile}");
                return Command::FAILURE;
            }
            $tarSize = round(File::size($buktiFile) / 1024, 2);
            $this->info("Bukti setor backup created successfully: {$buktiFile} ({$tarSize} KB)");
        }

        // 3. Cleanup files older than 14 days
        $this->info("3. Checking for backup files older than 14 days...");
        $retentionDays = 14;
        $retentionThreshold = Carbon::now()->subDays($retentionDays)->getTimestamp();
        $deletedCount = 0;

        if (File::isDirectory($backupDir)) {
            $files = File::files($backupDir);
            foreach ($files as $file) {
                $filename = $file->getFilename();
                // Match e_pool_db_*.sql.gz or e_pool_bukti_setor_*.tar.gz
                if (preg_match('/^e_pool_(db|bukti_setor)_\d{8}_\d{6}\.(sql\.gz|tar\.gz)$/', $filename)) {
                    $mtime = $file->getMTime();
                    if ($mtime < $retentionThreshold) {
                        $ageDays = round((time() - $mtime) / 86400, 1);
                        if ($dryRun) {
                            $this->warn("[Dry-Run] Would delete: {$filename} (age: {$ageDays} days)");
                        } else {
                            File::delete($file->getRealPath());
                            $this->warn("Deleted expired backup: {$filename} (age: {$ageDays} days)");
                        }
                        $deletedCount++;
                    }
                }
            }
        }

        if ($deletedCount === 0) {
            $this->info("No expired backups found (threshold: {$retentionDays} days).");
        } else {
            $this->info("Cleaned up {$deletedCount} expired backup file(s).");
        }

        $this->info($dryRun ? '--- Backup Simulation Finished Successfully ---' : '--- Backup Completed Successfully ---');
        return Command::SUCCESS;
    }
}
