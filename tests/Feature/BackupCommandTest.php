<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackupCommandTest extends TestCase
{
    public function test_backup_command_dry_run_executes_successfully(): void
    {
        $this->artisan('app:backup', ['--dry-run' => true])
            ->expectsOutputToContain('--- Starting e-pool Backup (DRY RUN) ---')
            ->expectsOutputToContain('1. Backing up MariaDB database...')
            ->expectsOutputToContain('[Dry-Run] Target database archive:')
            ->expectsOutputToContain('2. Backing up bukti setor files...')
            ->expectsOutputToContain('[Dry-Run] Target bukti setor archive:')
            ->expectsOutputToContain('3. Checking for backup files older than 14 days...')
            ->expectsOutputToContain('--- Backup Simulation Finished Successfully ---')
            ->assertSuccessful();
    }

    public function test_backup_command_is_registered_in_console(): void
    {
        $commands = Artisan::all();
        $this->assertArrayHasKey('app:backup', $commands);
    }
}
