<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database Laravel ke file .sql tanpa mysqldump';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $db = config('database.connections.mysql.database');
        $filename = 'backups/backup_' . now()->format('Ymd_His') . '.sql';

        $tables = DB::select('SHOW TABLES');
        $sqlDump = "-- Backup database: $db\n-- " . now() . "\n\n";

        foreach ($tables as $tableObj) {
            $table = array_values((array)$tableObj)[0];

            // Struktur tabel
            $createStmt = DB::select("SHOW CREATE TABLE `$table`")[0]->{'Create Table'};
            $sqlDump .= "DROP TABLE IF EXISTS `$table`;\n$createStmt;\n\n";

            // Data
            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $values = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    return "'" . addslashes($val) . "'";
                }, (array) $row);

                $sqlDump .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlDump .= "\n";
        }

        Storage::disk('local')->put($filename, $sqlDump);
        $this->info("Backup selesai: $filename");
    }
}
