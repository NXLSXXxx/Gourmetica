<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    protected string $backupDir;

    public function __construct()
    {
        $this->backupDir = storage_path('app/backups');
    }

    public function index()
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403, 'Acceso exclusivo para rol Ingeniero.');
        }

        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $files = array_diff(scandir($this->backupDir), ['.', '..']);
        $backups = [];

        foreach ($files as $file) {
            $filePath = $this->backupDir . '/' . $file;
            if (is_file($filePath)) {
                $bytes = filesize($filePath);
                $backups[] = [
                    'name' => $file,
                    'size' => $bytes >= 1048576 
                        ? round($bytes / 1048576, 2) . ' MB' 
                        : round($bytes / 1024, 2) . ' KB',
                    'date' => date('Y-m-d H:i:s', filemtime($filePath)),
                ];
            }
        }

        // Ordenar backups del más reciente al más antiguo
        usort($backups, fn($a, $b) => strcmp($b['date'], $a['date']));

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Genera un volcado SQL real y completo de la base de datos.
     */
    public function create()
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403, 'Acceso exclusivo para rol Ingeniero.');
        }

        if (!file_exists($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "backup_gourmetica_{$timestamp}.sql";
        $filePath = $this->backupDir . '/' . $filename;

        try {
            $handle = fopen($filePath, 'w+');
            if (!$handle) {
                throw new \Exception("No se pudo crear el archivo de respaldo en el disco.");
            }

            $dbName = config('database.connections.mysql.database') ?? env('DB_DATABASE', 'gourmetica');
            $pdo = DB::getPdo();

            // Encabezado del volcado SQL
            fwrite($handle, "-- ========================================================\n");
            fwrite($handle, "-- Gourmetica Database Backup\n");
            fwrite($handle, "-- Fecha: " . date('Y-m-d H:i:s') . "\n");
            fwrite($handle, "-- Base de datos: {$dbName}\n");
            fwrite($handle, "-- ========================================================\n\n");
            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
            fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
            fwrite($handle, "SET time_zone = \"+00:00\";\n\n");

            // Obtener todas las tablas
            $tables = DB::select('SHOW TABLES');
            $property = "Tables_in_{$dbName}";

            foreach ($tables as $tableObj) {
                $tableName = isset($tableObj->$property) 
                    ? $tableObj->$property 
                    : array_values((array)$tableObj)[0];

                // 1. Estructura de la tabla (CREATE TABLE)
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

                // 2. Datos de la tabla (INSERT INTO)
                $count = DB::table($tableName)->count();
                if ($count > 0) {
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

                            $sql = "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                            fwrite($handle, $sql);
                        }
                    });

                    fwrite($handle, "\n");
                }
            }

            fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fwrite($handle, "-- Fin del respaldo.\n");
            fclose($handle);

            Log::info("Backup generado con éxito: {$filename} por el usuario " . auth('admin')->user()->email);

            return redirect()->back()->with('success', 'Copia de seguridad real generada exitosamente: ' . $filename);
        } catch (\Throwable $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            Log::error("Error al generar backup: " . $e->getMessage());

            return redirect()->back()->with('error', 'Error al generar la copia de seguridad: ' . $e->getMessage());
        }
    }

    /**
     * Descarga de forma segura un archivo de backup validando la ruta.
     */
    public function download(string $filename)
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403, 'Acceso exclusivo para rol Ingeniero.');
        }

        // Sanitizar el nombre del archivo para prevenir Path Traversal
        $safeFilename = basename($filename);
        $filePath = $this->backupDir . '/' . $safeFilename;

        if (!file_exists($filePath) || !is_file($filePath)) {
            abort(404, 'El archivo de respaldo no existe.');
        }

        return response()->download($filePath, $safeFilename, [
            'Content-Type' => 'application/sql'
        ]);
    }

    /**
     * Elimina un archivo de backup existente.
     */
    public function destroy(string $filename)
    {
        if (!auth('admin')->user()->isIngeniero()) {
            abort(403, 'Acceso exclusivo para rol Ingeniero.');
        }

        $safeFilename = basename($filename);
        $filePath = $this->backupDir . '/' . $safeFilename;

        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
            return redirect()->back()->with('success', "Respaldo {$safeFilename} eliminado correctamente.");
        }

        return redirect()->back()->with('error', 'No se encontró el archivo a eliminar.');
    }
}
