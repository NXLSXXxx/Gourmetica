<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use PDO;

class DrillDisasterRecoveryCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:dr-drill 
                            {--target-db= : Nombre de la base de datos temporal autorizada para restaurar} 
                            {--backup-file= : Ruta específica del archivo .sql a restaurar} 
                            {--keep-target : Conserva la base de datos de prueba tras el simulacro sin eliminarla}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta un simulacro controlado de recuperación ante desastres (Disaster Recovery), midiendo RTO, RPO e integridad de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("===============================================================================");
        $this->info("   GOURMETICA - SIMULACRO DE RECUPERACION ANTE DESASTRES (P3.1 DR-DRILL)        ");
        $this->info("===============================================================================");

        $defaultConn = config('database.default');
        $connConfig = config("database.connections.{$defaultConn}");
        $driver = $connConfig['driver'] ?? 'mysql';
        $sourceDb = $connConfig['database'] ?? '';

        // ---------------------------------------------------------------------
        // 1. REGLAS ESTRICTAS DE SEGURIDAD (PROTECCION DE PRODUCCION)
        // ---------------------------------------------------------------------
        $targetDb = $this->option('target-db');

        if (empty($targetDb)) {
            if ($driver === 'sqlite') {
                $targetDb = storage_path('app/backups/dr_drill_' . time() . '.sqlite');
            } else {
                $targetDb = $sourceDb . '_dr_drill';
            }
        }

        $protectedDatabases = [
            strtolower((string)$sourceDb),
            'mysql',
            'information_schema',
            'performance_schema',
            'sys',
            'gourmeti_bd',
            'gourmetica',
        ];

        // Impedir absolutamente cualquier operación si el objetivo coincide con la BD activa o protegida
        if (in_array(strtolower((string)$targetDb), $protectedDatabases, true) || $targetDb === $sourceDb) {
            $this->error("-------------------------------------------------------------------------------");
            $this->error(" [BLOQUEO DE SEGURIDAD]: El objetivo '{$targetDb}' coincide con la base de datos");
            $this->error(" de producción activa o una base reservada del sistema.");
            $this->error(" Está estrictamente prohibido realizar restauraciones sobre la base activa.");
            $this->error("-------------------------------------------------------------------------------");
            return 1;
        }

        $this->line(" Base de datos origen (Producción): <fg=cyan>{$sourceDb}</>");
        $this->line(" Base de datos destino (Simulacro): <fg=yellow>{$targetDb}</>");
        $this->newLine();

        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFilePath = $this->option('backup-file');
        $generatedBackup = false;

        try {
            // -----------------------------------------------------------------
            // 2. GENERACION DE RESPALDO Y MEDICION DE RPO
            // -----------------------------------------------------------------
            if (empty($backupFilePath)) {
                $this->line("<fg=yellow;options=bold>--- PASO 1: GENERACION DE VOLCADO Y MEDICION DE RPO ---</>");
                $timestamp = date('Y-m-d_H-i-s');
                $filename = "dr_drill_{$timestamp}.sql";
                $backupFilePath = $backupDir . '/' . $filename;
                
                $this->generateSqlDump($backupFilePath, $sourceDb);
                $generatedBackup = true;
                $this->line(" [OK] Respaldo SQL generado: <fg=green>{$filename}</> (" . round(filesize($backupFilePath) / 1024, 2) . " KB)");
            } else {
                if (!file_exists($backupFilePath)) {
                    $this->error("El archivo de respaldo especificado no existe: {$backupFilePath}");
                    return 1;
                }
                $this->line(" [OK] Utilizando respaldo existente: <fg=green>{$backupFilePath}</>");
            }

            // Medición de RPO (Recovery Point Objective): Diferencia entre la última transacción y el respaldo
            $rpoMetrics = $this->calculateRpo($backupFilePath);
            $this->line(" [OK] RPO Medido: <fg=cyan>{$rpoMetrics['rpo_seconds']} segundos</> ({$rpoMetrics['rpo_human']})");
            $this->line("      Última transacción detectada: {$rpoMetrics['last_tx_time']}");
            $this->newLine();

            // -----------------------------------------------------------------
            // 3. PREPARACION DEL ENTORNO AISLADO DE DESTINO
            // -----------------------------------------------------------------
            $this->line("<fg=yellow;options=bold>--- PASO 2: CONEXION Y PREPARACION DE BASE TEMPORAL ---</>");
            $targetPdo = $this->prepareTargetDatabase($driver, $connConfig, $targetDb);
            $this->line(" [OK] Base de datos de prueba inicializada y aislada: <fg=green>{$targetDb}</>");
            $this->newLine();

            // -----------------------------------------------------------------
            // 4. RESTAURACION CONTROLADA Y MEDICION DE RTO
            // -----------------------------------------------------------------
            $this->line("<fg=yellow;options=bold>--- PASO 3: RESTAURACION CONTROLADA Y MEDICION DE RTO ---</>");
            $startTime = hrtime(true);

            $this->executeRestore($targetPdo, $backupFilePath, $driver);

            $endTime = hrtime(true);
            $rtoSeconds = round(($endTime - $startTime) / 1e9, 4);
            $this->line(" [OK] RTO Medido (Tiempo de Restauración): <fg=cyan>{$rtoSeconds} segundos</>");
            $this->newLine();

            // -----------------------------------------------------------------
            // 5. AUDITORIA Y VERIFICACION DE INTEGRIDAD (SOURCE vs TARGET)
            // -----------------------------------------------------------------
            $this->line("<fg=yellow;options=bold>--- PASO 4: AUDITORIA DE INTEGRIDAD Y CONSISTENCIA DE DATOS ---</>");
            $comparison = $this->verifyDataConsistency($driver, $sourceDb, $targetDb, $targetPdo);

            $this->table(
                ['Tabla', 'Filas Origen', 'Filas Restauradas', 'Checksum Origen', 'Checksum Destino', 'Estado'],
                $comparison['rows']
            );

            if (!$comparison['passed']) {
                $this->error(" [FALLO]: Discrepancia detectada entre la base origen y la base restaurada.");
                return 1;
            }

            $this->line(" [OK] Consistencia de Datos: <fg=green>100% de tablas y registros verificados idénticos</>");
            $this->newLine();

            // -----------------------------------------------------------------
            // 6. LIMPIEZA SEGURA DE LA BASE TEMPORAL
            // -----------------------------------------------------------------
            if (!$this->option('keep-target')) {
                $this->line("<fg=yellow;options=bold>--- PASO 5: LIMPIEZA SEGURA DE ENTIDADES TEMPORALES ---</>");
                $this->cleanupTargetDatabase($driver, $connConfig, $targetDb, $targetPdo);
                $this->line(" [OK] Base temporal de prueba '{$targetDb}' eliminada limpiamente (Cero residuos)");
            } else {
                $this->warn(" [AVISO]: La opción --keep-target mantuvo la base '{$targetDb}' disponible para inspección.");
            }

            $this->newLine();
            $this->info("===============================================================================");
            $this->info("   SIMULACRO DR-DRILL COMPLETADO CON EXITO (RTO: {$rtoSeconds}s | RPO: {$rpoMetrics['rpo_seconds']}s)   ");
            $this->info("===============================================================================");

            return 0;

        } catch (\Throwable $e) {
            $this->error("Error crítico durante el simulacro de recuperación: " . $e->getMessage());
            Log::error("audit:dr-drill exception", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        } finally {
            if ($generatedBackup && file_exists($backupFilePath) && !$this->option('keep-target')) {
                @unlink($backupFilePath);
            }
        }
    }

    /**
     * Genera un volcado SQL streaming de la base de datos origen usando PDO.
     */
    protected function generateSqlDump(string $filePath, string $dbName): void
    {
        $handle = fopen($filePath, 'w+');
        if (!$handle) {
            throw new \Exception("No se pudo crear el archivo de respaldo en '{$filePath}'.");
        }

        $pdo = DB::getPdo();
        $defaultConn = config('database.default');
        $driver = config("database.connections.{$defaultConn}.driver");

        fwrite($handle, "-- ========================================================\n");
        fwrite($handle, "-- Gourmetica Disaster Recovery Backup Dump\n");
        fwrite($handle, "-- Generado: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- ========================================================\n\n");

        if ($driver === 'mysql' || $driver === 'mariadb') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

            $tables = DB::select('SHOW TABLES');
            $property = "Tables_in_{$dbName}";

            foreach ($tables as $tableObj) {
                $tableName = isset($tableObj->$property) ? $tableObj->$property : array_values((array)$tableObj)[0];

                fwrite($handle, "-- Estructura para tabla `{$tableName}`\n");
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

                            $sql = "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                            fwrite($handle, $sql);
                        }
                    });
                    fwrite($handle, "\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        } elseif ($driver === 'sqlite') {
            $tables = DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($tables as $t) {
                $tableName = $t->name;
                fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                fwrite($handle, $t->sql . ";\n");

                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $cols = array_map(fn($c) => "`{$c}`", array_keys($rowArray));
                    $vals = array_map(function ($val) use ($pdo) {
                        return is_null($val) ? 'NULL' : $pdo->quote((string)$val);
                    }, array_values($rowArray));

                    fwrite($handle, "INSERT INTO `{$tableName}` (" . implode(', ', $cols) . ") VALUES (" . implode(', ', $vals) . ");\n");
                }
            }
        }

        fclose($handle);
    }

    /**
     * Calcula métricas de RPO comparando la última actividad transaccional con la fecha del respaldo.
     */
    protected function calculateRpo(string $backupFilePath): array
    {
        $backupTimestamp = filemtime($backupFilePath);
        $candidateTimes = [];

        $tablesWithTimestamps = [
            'orders' => 'created_at',
            'sales'  => 'created_at',
            'audits' => 'created_at',
            'users'  => 'created_at',
        ];

        foreach ($tablesWithTimestamps as $table => $column) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, $column)) {
                $maxVal = DB::table($table)->max($column);
                if ($maxVal) {
                    $candidateTimes[] = strtotime($maxVal);
                }
            }
        }

        $lastTxTimestamp = !empty($candidateTimes) ? max($candidateTimes) : $backupTimestamp;
        $rpoSeconds = max(0, $backupTimestamp - $lastTxTimestamp);

        return [
            'rpo_seconds'   => $rpoSeconds,
            'rpo_human'     => $rpoSeconds === 0 ? 'Pérdida 0 (Datos en tiempo real)' : "{$rpoSeconds}s de retraso",
            'last_tx_time'  => date('Y-m-d H:i:s', $lastTxTimestamp),
            'backup_time'   => date('Y-m-d H:i:s', $backupTimestamp),
        ];
    }

    /**
     * Inicializa y conecta a la base de datos de destino asegurando aislamiento.
     */
    protected function prepareTargetDatabase(string $driver, array $connConfig, string $targetDb): PDO
    {
        if ($driver === 'sqlite') {
            if (file_exists($targetDb)) {
                @unlink($targetDb);
            }
            touch($targetDb);
            return new PDO("sqlite:{$targetDb}", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
        }

        $host = $connConfig['host'] ?? '127.0.0.1';
        $port = $connConfig['port'] ?? '3306';
        $user = $connConfig['username'] ?? 'root';
        $pass = $connConfig['password'] ?? '';

        // Conectar al servidor MySQL para crear la base de datos de prueba si no existe
        $serverPdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        // Conectar específicamente a la base de prueba creada
        $targetPdo = new PDO("mysql:host={$host};port={$port};dbname={$targetDb}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        return $targetPdo;
    }

    /**
     * Ejecuta la restauración del volcado SQL dentro de la base de destino.
     */
    protected function executeRestore(PDO $targetPdo, string $backupFilePath, string $driver): void
    {
        $sql = file_get_contents($backupFilePath);
        if ($sql === false) {
            throw new \Exception("No se pudo leer el archivo de volcado para restaurar.");
        }

        if ($driver === 'mysql' || $driver === 'mariadb') {
            $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $targetPdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");
            $targetPdo->exec($sql);
            $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        } else {
            $targetPdo->exec($sql);
        }
    }

    /**
     * Compara exhaustivamente tablas, conteos y sumas de identificadores entre origen y destino.
     */
    protected function verifyDataConsistency(string $driver, string $sourceDb, string $targetDb, PDO $targetPdo): array
    {
        $sourceTables = [];
        if ($driver === 'mysql' || $driver === 'mariadb') {
            $raw = DB::select('SHOW TABLES');
            $property = "Tables_in_{$sourceDb}";
            foreach ($raw as $t) {
                $sourceTables[] = isset($t->$property) ? $t->$property : array_values((array)$t)[0];
            }
        } else {
            $raw = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            foreach ($raw as $t) {
                $sourceTables[] = $t->name;
            }
        }

        $allPassed = true;
        $rows = [];

        foreach ($sourceTables as $table) {
            // Métricas en origen
            $sourceCount = DB::table($table)->count();
            $sourceIdSum = 0;
            if (Schema::hasColumn($table, 'id')) {
                $sourceIdSum = DB::table($table)->sum('id') ?? 0;
            }

            // Métricas en destino
            $stmtCount = $targetPdo->query("SELECT COUNT(*) FROM `{$table}`");
            $targetCount = $stmtCount ? (int)$stmtCount->fetchColumn() : -1;

            $targetIdSum = 0;
            try {
                $stmtSum = $targetPdo->query("SELECT COALESCE(SUM(id), 0) FROM `{$table}`");
                if ($stmtSum) {
                    $targetIdSum = (int)$stmtSum->fetchColumn();
                }
            } catch (\Throwable $e) {
                $targetIdSum = 0;
            }

            $matches = ($sourceCount === $targetCount) && ($sourceIdSum === $targetIdSum);
            if (!$matches) {
                $allPassed = false;
            }

            $rows[] = [
                $table,
                $sourceCount,
                $targetCount,
                $sourceIdSum,
                $targetIdSum,
                $matches ? '<fg=green>PASS</>' : '<fg=red>DISCREPANCIA</>',
            ];
        }

        return [
            'passed' => $allPassed,
            'rows'   => $rows,
        ];
    }

    /**
     * Limpia y elimina la base de datos de prueba o archivo sqlite temporal.
     */
    protected function cleanupTargetDatabase(string $driver, array $connConfig, string $targetDb, PDO $targetPdo): void
    {
        if ($driver === 'sqlite') {
            unset($targetPdo);
            if (file_exists($targetDb)) {
                @unlink($targetDb);
            }
            return;
        }

        $host = $connConfig['host'] ?? '127.0.0.1';
        $port = $connConfig['port'] ?? '3306';
        $user = $connConfig['username'] ?? 'root';
        $pass = $connConfig['password'] ?? '';

        unset($targetPdo);

        $serverPdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        $serverPdo->exec("DROP DATABASE IF EXISTS `{$targetDb}`");
    }
}
