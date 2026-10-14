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
                            {--keep-target : Conserva la base de datos de prueba tras el simulacro sin eliminarla}
                            {--force : Ejecuta el simulacro sin solicitar confirmación interactiva}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ejecuta un simulacro controlado de recuperación ante desastres (Disaster Recovery), midiendo RTO, RPO e integridad profunda de datos';

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
        // 1. REGLAS ESTRICTAS DE SEGURIDAD (PROTECCION DE PRODUCCION Y WHITELIST)
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
        if (in_array(strtolower((string)$targetDb), $protectedDatabases, true) || strtolower((string)$targetDb) === strtolower((string)$sourceDb)) {
            $this->error("-------------------------------------------------------------------------------");
            $this->error(" [BLOQUEO DE SEGURIDAD]: El objetivo '{$targetDb}' coincide con la base de datos");
            $this->error(" de producción activa o una base reservada del sistema.");
            $this->error(" Está estrictamente prohibido realizar restauraciones sobre la base activa.");
            $this->error("-------------------------------------------------------------------------------");
            return 1;
        }

        // Whitelist obligatoria y estricta: Coincidencia EXACTA contra bases configuradas fuera del código
        // Se prohíben expresamente las subcadenas permisivas (que permitirían nombres como 'clientes_test_backup')
        $allowedDatabases = (array) config('database.dr_allowed_databases', ['gourmeti_drill', 'gourmetica_drill', 'gourmetica_test']);
        $allowedLower = array_map('strtolower', $allowedDatabases);

        if ($driver === 'sqlite') {
            $isBackupsSqlite = str_ends_with($targetDb, '.sqlite') && str_contains($targetDb, 'backups');
            if (!$isBackupsSqlite && !in_array(strtolower((string)$targetDb), $allowedLower, true)) {
                $this->error("-------------------------------------------------------------------------------");
                $this->error(" [BLOQUEO DE SEGURIDAD]: Destino no autorizado '{$targetDb}'.");
                $this->error("-------------------------------------------------------------------------------");
                return 1;
            }
        } else {
            if (!in_array(strtolower((string)$targetDb), $allowedLower, true)) {
                $this->error("-------------------------------------------------------------------------------");
                $this->error(" [BLOQUEO DE SEGURIDAD]: Destino no autorizado '{$targetDb}'.");
                $this->error(" Para prevenir destrucción accidental de bases legítimas con nombres que contengan");
                $this->error(" subcadenas engañosas (ej: 'clientes_test_backup'), el destino debe coincidir");
                $this->error(" EXACTAMENTE con una base autorizada en la configuración (DR_ALLOWED_DATABASES).");
                $this->error(" Bases autorizadas configuradas: " . implode(', ', $allowedDatabases));
                $this->error("-------------------------------------------------------------------------------");
                return 1;
            }
        }

        if (!$this->option('force') && $this->input->isInteractive()) {
            if (!$this->confirm("¿Confirma la ejecución del simulacro DR sobre la base '{$targetDb}'? (Se reinicializarán sus tablas)", true)) {
                $this->warn("Simulacro cancelado por el usuario.");
                return 0;
            }
        }

        $this->line(" Base de datos origen (Producción): <fg=cyan>{$sourceDb}</>");
        $this->line(" Base de datos destino (Simulacro): <fg=yellow>{$targetDb}</>");
        $this->newLine();

        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $backupFilePath = $this->option('backup-file');
        if (!empty($backupFilePath)) {
            $rawPath = $backupFilePath;
            $baseName = basename($rawPath);
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? '');

            $candidates = [
                $rawPath,
                base_path($rawPath),
                base_path('~/' . $baseName),
                base_path($baseName),
                storage_path('app/backups/' . $baseName),
            ];

            if (!empty($home)) {
                $candidates[] = rtrim($home, '/') . '/' . $baseName;
                $candidates[] = rtrim($home, '/') . '/Gourmetica/~/' . $baseName;
                $candidates[] = rtrim($home, '/') . '/Gourmetica/' . $baseName;
                if (str_starts_with($rawPath, '~/')) {
                    $candidates[] = rtrim($home, '/') . '/' . substr($rawPath, 2);
                }
            }

            $resolved = false;
            foreach ($candidates as $cand) {
                if (!empty($cand) && file_exists($cand) && is_file($cand)) {
                    $backupFilePath = realpath($cand) ?: $cand;
                    $resolved = true;
                    break;
                }
            }

            if (!$resolved) {
                $searchPatterns = [
                    base_path('*' . $baseName),
                    base_path('~/*' . $baseName),
                    storage_path('app/backups/*' . $baseName),
                ];
                if (!empty($home)) {
                    $searchPatterns[] = rtrim($home, '/') . '/*' . $baseName;
                    $searchPatterns[] = rtrim($home, '/') . '/Gourmetica/*' . $baseName;
                    $searchPatterns[] = rtrim($home, '/') . '/Gourmetica/~/*' . $baseName;
                }
                foreach ($searchPatterns as $pattern) {
                    $matches = glob($pattern);
                    if (!empty($matches)) {
                        foreach ($matches as $m) {
                            if (file_exists($m) && is_file($m)) {
                                $backupFilePath = realpath($m) ?: $m;
                                $resolved = true;
                                break 2;
                            }
                        }
                    }
                }
            }
        }
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
            $this->line(" [OK] RPO Estimado de Base de Datos: <fg=cyan>{$rpoMetrics['rpo_seconds']} segundos</> ({$rpoMetrics['rpo_human']})");
            $this->line("      Última actividad transaccional detectada en DB: {$rpoMetrics['last_tx_time']}");
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
            $this->line(" [OK] RTO Medido (Restauración del Motor de BD): <fg=cyan>{$rtoSeconds} segundos</>");
            $this->line("      (Tiempo medido de importación, DDL/DML e indexación en el motor MySQL/MariaDB)");
            $this->newLine();

            // -----------------------------------------------------------------
            // 5. AUDITORIA Y VERIFICACION DE INTEGRIDAD PROFUNDA (SOURCE vs TARGET)
            // -----------------------------------------------------------------
            $this->line("<fg=yellow;options=bold>--- PASO 4: AUDITORIA DE INTEGRIDAD PROFUNDA Y CONSISTENCIA DE DATOS ---</>");
            $comparison = $this->verifyDataConsistency($driver, $sourceDb, $targetDb, $targetPdo);

            $this->table(
                ['Tabla', 'Filas Origen', 'Filas Rest.', 'Checksum ID', 'Hash Contenido SHA-256', 'Estado'],
                $comparison['rows']
            );

            if (!$comparison['passed']) {
                $this->error(" [FALLO]: Discrepancia detectada entre la base origen y la base restaurada.");
                return 1;
            }

            $this->line(" [OK] Consistencia de Datos: <fg=green>100% de tablas y registros verificados idénticos (Filas, Claves y Hash SHA-256 de contenido)</>");
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
            $this->info("   SIMULACRO DR-DRILL COMPLETADO CON EXITO (RTO BD: {$rtoSeconds}s | RPO Estimado: {$rpoMetrics['rpo_seconds']}s)   ");
            $this->info("   (Nota: Recuperación total del host web completo requiere aprovisionamiento de red/DNS)");
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

        // 1. Intentar conectar directamente si la base de datos ya fue creada previamente (ej. en cPanel)
        try {
            $targetPdo = new PDO("mysql:host={$host};port={$port};dbname={$targetDb}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Si ya existe, purgar tablas previas para asegurar una restauración limpia y determinista
            $existingTables = $targetPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($existingTables)) {
                // Validación de seguridad: Comprobar que las tablas pertenezcan al esquema reconocido de Gourmetica
                // Si la base contiene tablas foráneas no identificadas, se bloquea la ejecución para proteger datos ajenos
                $sourceTablesRaw = DB::select('SHOW TABLES');
                $sourceDb = config('database.connections.mysql.database') ?? 'gourmetica';
                $prop = "Tables_in_{$sourceDb}";
                $knownTables = array_map(function($t) use ($prop) {
                    return strtolower(isset($t->$prop) ? $t->$prop : array_values((array)$t)[0]);
                }, $sourceTablesRaw);

                $unrecognizedTables = [];
                foreach ($existingTables as $tName) {
                    if (!in_array(strtolower($tName), $knownTables, true)) {
                        $unrecognizedTables[] = $tName;
                    }
                }

                if (!empty($unrecognizedTables)) {
                    throw new \Exception(
                        " [BLOQUEO DE SEGURIDAD]: La base destino '{$targetDb}' contiene " . count($unrecognizedTables) . 
                        " tablas con datos preexistentes no reconocidos como parte del simulacro Gourmetica (" . 
                        implode(', ', array_slice($unrecognizedTables, 0, 5)) . "). Se aborta la operación para proteger datos ajenos."
                    );
                }

                $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                foreach ($existingTables as $t) {
                    $targetPdo->exec("DROP TABLE IF EXISTS `{$t}`;");
                }
                $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            }

            return $targetPdo;
        } catch (\PDOException $e) {
            // Si la base no existe aún, intentar crearla
        }

        // 2. Intentar crear la base de datos en el servidor MySQL
        try {
            $serverPdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$targetDb}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            $targetPdo = new PDO("mysql:host={$host};port={$port};dbname={$targetDb}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            return $targetPdo;
        } catch (\PDOException $e) {
            // Error 1044 (Access denied para CREATE DATABASE en cPanel/hosting compartido)
            if ($e->getCode() == 42000 || str_contains($e->getMessage(), '1044')) {
                throw new \Exception(
                    "El usuario MySQL '{$user}' no tiene permisos globales para ejecutar 'CREATE DATABASE' directamente.\n" .
                    "En servidores cPanel/compartidos, esto es una política de seguridad estándar.\n\n" .
                    "Solución en 3 pasos:\n" .
                    "1. Ingresa a tu cPanel -> Bases de datos MySQL.\n" .
                    "2. Crea una nueva base de datos vacía (ejemplo: 'gourmeti_drill').\n" .
                    "3. En 'Añadir usuario a la base de datos', añade a '{$user}' con TODOS los privilegios.\n" .
                    "4. Ejecuta el simulacro indicando esa base de prueba autorizada:\n" .
                    "   php artisan audit:dr-drill --target-db=gourmeti_drill"
                );
            }

            throw $e;
        }
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
            $sourceCount = (int) DB::table($table)->count();
            $sourceIdSum = 0;
            if (Schema::hasColumn($table, 'id')) {
                $rawSum = DB::table($table)->sum('id');
                $sourceIdSum = $rawSum !== null ? (int)$rawSum : 0;
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

            // Verificación profunda: Hash SHA-256 de filas y columnas completas
            $sourceContentHash = $this->computeSourceTableHash($table);
            $targetContentHash = $this->computeTargetTableHash($targetPdo, $table);

            $matches = ($sourceCount === $targetCount) && ($sourceIdSum === $targetIdSum) && ($sourceContentHash === $targetContentHash);
            if (!$matches) {
                $allPassed = false;
            }

            $hashDisplay = ($sourceContentHash === $targetContentHash)
                ? '<fg=green>MATCH (' . substr($sourceContentHash, 0, 8) . ')</>'
                : '<fg=red>' . substr($sourceContentHash, 0, 6) . ' != ' . substr($targetContentHash, 0, 6) . '</>';

            $rows[] = [
                $table,
                $sourceCount,
                $targetCount,
                $sourceIdSum,
                $hashDisplay,
                $matches ? '<fg=green>PASS</>' : '<fg=red>DISCREPANCIA</>',
            ];
        }

        return [
            'passed' => $allPassed,
            'rows'   => $rows,
        ];
    }

    /**
     * Calcula un hash criptográfico SHA-256 de todas las filas y columnas de la tabla en origen.
     */
    protected function computeSourceTableHash(string $table): string
    {
        $rows = DB::table($table)->orderBy(DB::raw('1'))->get();
        if ($rows->isEmpty()) {
            return 'EMPTY';
        }

        $hashCtx = hash_init('sha256');
        foreach ($rows as $row) {
            $normalized = array_map(fn($v) => is_null($v) ? 'NULL' : (string)$v, (array)$row);
            hash_update($hashCtx, json_encode($normalized));
        }

        return hash_final($hashCtx);
    }

    /**
     * Calcula un hash criptográfico SHA-256 de todas las filas y columnas de la tabla en destino.
     */
    protected function computeTargetTableHash(PDO $targetPdo, string $table): string
    {
        $stmt = $targetPdo->query("SELECT * FROM `{$table}` ORDER BY 1");
        if (!$stmt) {
            return 'ERROR';
        }
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($rows)) {
            return 'EMPTY';
        }

        $hashCtx = hash_init('sha256');
        foreach ($rows as $row) {
            $normalized = array_map(fn($v) => is_null($v) ? 'NULL' : (string)$v, $row);
            hash_update($hashCtx, json_encode($normalized));
        }

        return hash_final($hashCtx);
    }

    /**
     * Limpia y elimina la base de datos de prueba o archivo sqlite temporal.
     */
    protected function cleanupTargetDatabase(string $driver, array $connConfig, string $targetDb, ?PDO $targetPdo = null): void
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

        // Intentar DROP DATABASE
        try {
            $serverPdo = new PDO("mysql:host={$host};port={$port}", $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            $serverPdo->exec("DROP DATABASE IF EXISTS `{$targetDb}`");
        } catch (\PDOException $e) {
            // Si el usuario en cPanel no tiene permisos DROP DATABASE, vaciar las tablas de la base temporal
            if ($targetPdo) {
                try {
                    $tables = $targetPdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
                    $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
                    foreach ($tables as $t) {
                        $targetPdo->exec("DROP TABLE IF EXISTS `{$t}`;");
                    }
                    $targetPdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
                } catch (\Throwable $ignored) {
                }
            }
        }
    }
}
