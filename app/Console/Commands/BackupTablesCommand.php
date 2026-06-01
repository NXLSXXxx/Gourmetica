<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupTablesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:backup-tables';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
        $dbName = config('database.connections.mysql.database');
        $property = "Tables_in_{$dbName}";

        foreach ($tables as $table) {
            $tableName = $table->$property;
            $data = \Illuminate\Support\Facades\DB::table($tableName)->get();
            
            $filename = "backup_{$tableName}_" . now()->format('Y-m-d_H-i-s') . ".json";
            \Illuminate\Support\Facades\Storage::disk('local')->put("backups/{$filename}", $data->toJson());
            
            $this->info("Backed up table: {$tableName}");
        }

        $this->info('All tables backed up successfully.');
    }
}
