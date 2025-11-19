<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class BackupController extends Controller
{
    public function store(Request $request)
    {
        $timestamp = now()->format('Ymd_His');
        $dir = "backups/{$timestamp}";
        Storage::makeDirectory($dir);

    // Accept tables list and format. Default to xlsx per request.
    // If no specific tables requested, get all tables from database
    $defaultTables = $this->getAllDatabaseTables();
    $tables = $request->input('tables', $defaultTables);
    $format = $request->input('format', 'xlsx');

        $created = [];
        $downloadRoute = route('admin.backup.download', ['dir' => $timestamp]);

        // If XLSX requested, build workbook with one sheet per table and store it
        if ($format === 'xlsx') {
            $sheets = [];
            foreach ($tables as $t) {
                if (!DB::getSchemaBuilder()->hasTable($t)) continue;
                $rows = DB::table($t)->get()->map(function($r){ return (array) $r; })->toArray();
                // ensure at least empty sheet
                $sheets[$t] = $rows;
            }

            if (!empty($sheets)) {
                $xlsxPath = "{$dir}/backup_{$timestamp}.xlsx";
                try {
                    // Build a dynamic export with multiple sheets using anonymous classes
                    $export = new class($sheets) implements WithMultipleSheets {
                        private $sheetsData;
                        public function __construct($sheetsData) { $this->sheetsData = $sheetsData; }
                        public function sheets(): array {
                            $sheets = [];
                            foreach ($this->sheetsData as $title => $rows) {
                                $sheets[] = new class($title, $rows) implements FromArray, WithTitle {
                                    private $title;
                                    private $rows;
                                    public function __construct($title, $rows) { $this->title = $title; $this->rows = $rows; }
                                    public function array(): array { return $this->rows; }
                                    public function title(): string { return substr($this->title, 0, 31); }
                                };
                            }
                            return $sheets;
                        }
                    };
                    // store to local storage (storage/app)
                    Excel::store($export, $xlsxPath, 'local');
                    $created[] = $xlsxPath;
                    // provide direct download link for the xlsx file
                    $downloadRoute .= '?file=' . urlencode(basename($xlsxPath));
                } catch (\Throwable $e) {
                    // If Excel is not available or fails, fallback to CSV files
                    logger()->warning('Excel export failed, falling back to CSV: ' . $e->getMessage());
                    foreach ($sheets as $t => $rows) {
                        // write CSV
                        $fp = fopen('php://temp', 'r+');
                        $headings = [];
                        if (!empty($rows)) {
                            $headings = array_keys($rows[0]);
                            fputcsv($fp, $headings);
                        }
                        foreach ($rows as $row) {
                            $values = [];
                            foreach ($headings as $h) {
                                $values[] = $row[$h] ?? null;
                            }
                            fputcsv($fp, $values);
                        }
                        rewind($fp);
                        $contents = stream_get_contents($fp);
                        fclose($fp);
                        $path = "{$dir}/{$t}.csv";
                        Storage::put($path, $contents);
                        $created[] = $path;
                    }
                    // append warning to index
                    $indexWarning = 'Excel export failed: ' . $e->getMessage();
                }
            }

            // create index and return
            $index = [
                'created_at' => now()->toDateTimeString(), 
                'format' => $format, 
                'files' => $created,
                'tables_backed_up' => array_keys($sheets),
                'total_tables' => count($sheets)
            ];
            if (isset($indexWarning)) $index['warning'] = $indexWarning;
            $indexPath = "{$dir}/index.json";
            Storage::put($indexPath, json_encode($index, JSON_PRETTY_PRINT));

            return response()->json([
                'ok' => true, 
                'index' => $index, 
                'download' => $downloadRoute,
                'tables_count' => count($sheets),
                'tables' => array_keys($sheets),
                'files' => $created
            ]);
        }
        foreach ($tables as $t) {
            if (!DB::getSchemaBuilder()->hasTable($t)) {
                continue;
            }
            $rows = DB::table($t)->get();

            if ($format === 'csv') {
                // build CSV using fputcsv to a temp stream
                $fp = fopen('php://temp', 'r+');
                // headings
                $headings = [];
                if ($rows->count() > 0) {
                    $headings = array_keys((array) $rows->first());
                    fputcsv($fp, $headings);
                }
                foreach ($rows as $row) {
                    $values = [];
                    foreach ($headings as $h) {
                        $values[] = isset($row->{$h}) ? $row->{$h} : null;
                    }
                    fputcsv($fp, $values);
                }
                rewind($fp);
                $contents = stream_get_contents($fp);
                fclose($fp);
                $path = "{$dir}/{$t}.csv";
                Storage::put($path, $contents);
                $created[] = $path;
            } else {
                // default: JSON snapshot
                $path = "{$dir}/{$t}.json";
                Storage::put($path, $rows->toJson(JSON_PRETTY_PRINT));
                $created[] = $path;
            }
        }

        // Create a small index file
        $index = [
            'created_at' => now()->toDateTimeString(), 
            'format' => $format, 
            'files' => $created,
            'tables_backed_up' => $tables,
            'total_tables' => count($tables)
        ];
        $indexPath = "{$dir}/index.json";
        Storage::put($indexPath, json_encode($index, JSON_PRETTY_PRINT));

        return response()->json([
            'ok' => true, 
            'index' => $index, 
            'download' => route('admin.backup.download', ['dir' => $timestamp]),
            'tables_count' => count($tables),
            'tables' => $tables,
            'files' => $created
        ]);
    }

    public function download(string $dir)
    {
        $base = "backups/{$dir}";
        if (!Storage::exists("{$base}/index.json")) {
            abort(404);
        }
        // Allow downloading a specific file via ?file=filename, otherwise return the index
        $file = request()->query('file');
        if ($file) {
            $filePath = "{$base}/{$file}";
            if (!Storage::exists($filePath)) {
                abort(404);
            }
            return Storage::download($filePath, basename($filePath));
        }

        return Storage::download("{$base}/index.json", "backup_{$dir}_index.json");
    }

    /**
     * Get all tables from the current database
     */
    private function getAllDatabaseTables()
    {
        try {
            // Get the database name from config
            $database = config('database.connections.' . config('database.default') . '.database');
            
            // Query to get all tables from the current database
            $tables = DB::select("SELECT table_name as `table_name` FROM information_schema.tables WHERE table_schema = ?", [$database]);
            
            // Extract table names and filter out system tables we might not want to backup
            $tableNames = collect($tables)->pluck('table_name')->toArray();
            
            // Filter out Laravel migration and cache tables (optional)
            $excludeTables = ['migrations', 'failed_jobs', 'cache', 'cache_locks', 'sessions'];
            $tableNames = array_diff($tableNames, $excludeTables);
            
            return array_values($tableNames);
            
        } catch (\Throwable $e) {
            // Fallback to original tables if there's an error
            logger()->warning('Failed to get all database tables: ' . $e->getMessage());
            return ['users', 'patients', 'appointments'];
        }
    }
}
