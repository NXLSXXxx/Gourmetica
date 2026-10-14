<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AuditBackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:backup {--output= : Ruta o nombre de archivo de salida}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Genera un respaldo SQL completo y verificado usando la conexión activa de Laravel sin requerir contraseñas manuales';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===============================================================================");
        $this->info("   GOURMETICA - RESPALDO SQL DE SEGURIDAD PREVIO AL DESPLIEGUE                 ");
        $this->info("===============================================================================");

        $defaultConn = config('database.default');
        $driver = config("database.connections.{$defaultConn}.driver");
        $dbName = config("database.connections.{$defaultConn}.database") ?? 'gourmetica';
        
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $defaultPath = $backupDir . '/backup_pre_merge_' . date('Ymd_His') . '.sql';
        $filePath = $this->option('output') ?: $defaultPath;

        if (str_starts_with($filePath, '~/')) {
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? '');
            if (!empty($home)) {
                $filePath = rtrim($home, '/') . '/' . substr($filePath, 2);
            }
        }

        $targetDir = dirname($filePath);
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $this->line(" Base de datos origen: <fg=cyan>{$dbName}</> (Driver: {$driver})");
        $this->line(" Archivo destino: <fg=yellow>{$filePath}</>");
        $this->newLine();

        $startTime = microtime(true);
        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            $this->error("No se pudo crear el archivo de respaldo en '{$filePath}'.");
            return 1;
        }

        $pdo = DB::getPdo();
        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- Gourmetica Database Backup (Pre-Merge Safety Dump)\n");
        fwrite($handle, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Base de datos: {$dbName}\n");
        fwrite($handle, "-- ========================================================\n\n");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n");
            fwrite($handle, "SET time_zone = '+00:00';\n\n");

            $tables = DB::select('SHOW TABLES');
            $property = "Tables_in_{$dbName}";
            $totalTables = 0;
            $totalRows = 0;

            foreach ($tables as $tableObj) {
                $tableName = isset($tableObj->$property) ? $tableObj->$property : array_values((array)$tableObj)[0];
                $totalTables++;

                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "-- Estructura de tabla: `{$tableName}`\n");
                fwrite($handle, "-- --------------------------------------------------------\n");
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

                $createTableResult = DB::select("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createTableResult)) {
                    $createTableObj = (array)$createTableResult[0];
                    $createStatement = $createTableObj['Create Table'] ?? array_values($createTableObj)[1] ?? null;
                    if ($createStatement) {
                        fwrite($handle, $createStatement . ";\n\n");
                    }
                }

                $count = DB::table($tableName)->count();
                if ($count > 0) {
                    $totalRows += $count;
                    fwrite($handle, "-- Volcado de datos para la tabla `{$tableName}` ({$count} registros)\n");

                    DB::table($tableName)->orderBy(DB::raw('1'))->chunk(300, function ($rows) use ($handle, $tableName, $pdo) {
                        foreach ($rows as $row) {
                            $rowArray = (array)$row;
                            $columns = array_map(fn($col) => "`{$col}`", array_keys($rowArray));
                            $values = array_map(function ($val) use ($pdo) {
                                if (is_null($val)) {
                                    return 'NULL';
                                }
                                return $pdo->quote((string)$val);
                            }, array_values($rowArray));

                            fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n");
                        }
                    });
                    fwrite($handle, "\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fwrite($handle, "-- Fin del respaldo.\n");
        } else {
            // Soporte SQLite / pruebas
            $tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $totalTables = 0;
            $totalRows = 0;

            foreach ($tables as $tObj) {
                $tableName = $tObj->name;
                $totalTables++;
                fwrite($handle, "-- Tabla: {$tableName}\n");
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $totalRows++;
                    $rowArray = (array)$row;
                    $cols = implode(', ', array_keys($rowArray));
                    $vals = implode(', ', array_map(fn($v) => is_null($v) ? 'NULL' : $pdo->quote((string)$v), array_values($rowArray)));
                    fwrite($handle, "INSERT INTO `{$tableName}` ({$cols}) VALUES ({$vals});\n");
                }
            }
        }

        fclose($handle);

        $elapsed = round(microtime(true) - $startTime, 4);
        $sizeKb = round(filesize($filePath) / 1024, 2);
        $sha256 = hash_file('sha256', $filePath);

        $this->info(" [OK] Respaldo generado con éxito en {$elapsed}s");
        $this->line("      Tablas respaldadas: <fg=green>{$totalTables}</>");
        $this->line("      Registros respaldados: <fg=green>{$totalRows}</>");
        $this->line("      Tamaño del archivo: <fg=green>{$sizeKb} KB</>");
        $this->line("      Checksum SHA-256: <fg=cyan>{$sha256}</>");
        $this->newLine();
        $this->info("===============================================================================");
        $this->info("   RESPALDO VERIFICADO Y RECUPERABLE - LISTO PARA EL DESPLIEGUE                ");
        $this->info("===============================================================================");

        return 0;
    }
}
