<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditBackupTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Prueba que el comando audit:backup genere exitosamente un volcado SQL y calcule su checksum.
     */
    public function test_audit_backup_generates_verified_sql_dump(): void
    {
        $testPath = storage_path('app/backups/test_backup_' . time() . '.sql');

        $this->artisan('audit:backup', ['--output' => $testPath])
            ->expectsOutputToContain('RESPALDO VERIFICADO Y RECUPERABLE')
            ->expectsOutputToContain('Checksum SHA-256')
            ->assertExitCode(0);

        $this->assertFileExists($testPath);
        $this->assertGreaterThan(0, filesize($testPath));

        @unlink($testPath);
    }
}
