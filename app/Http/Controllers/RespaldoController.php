<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class RespaldoController extends Controller
{
    protected $disk = 'local';

    public function index()
    {
        if (!Storage::disk($this->disk)->exists('backups')) {
            Storage::disk($this->disk)->makeDirectory('backups');
        }

        $files = Storage::disk($this->disk)->files('backups');
        $respaldos = [];

        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) !== 'sql') continue;

            $respaldos[] = [
                'nombre'    => basename($file),
                'tamaño'    => round(Storage::disk($this->disk)->size($file) / 1024 / 1024, 2) . ' MB',
                'timestamp' => Storage::disk($this->disk)->lastModified($file),
                'fecha'     => date('d/m/Y H:i:s', Storage::disk($this->disk)->lastModified($file))
            ];
        }

        usort($respaldos, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        return view('cpanel.Respaldo.respaldo', compact('respaldos'));
    }

    public function store()
    {
        if (session_id()) {
            session_write_close();
        }
        set_time_limit(300);

        $filename = "respaldo_" . date('Y-m-d_H-i-s') . ".sql";
        $backupDirectory = Storage::disk($this->disk)->path('backups');

        if (!file_exists($backupDirectory)) {
            mkdir($backupDirectory, 0755, true);
        }

        $fullPath = $backupDirectory . DIRECTORY_SEPARATOR . $filename;

        try {
            $tables = \DB::select('SHOW TABLES');
            $dbNameAttr = "Tables_in_" . config('database.connections.mysql.database');

            $sqlScript = "-- Respaldo generado manualmente desde Hostinger\n";
            $sqlScript .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$dbNameAttr;

                $createTableStructure = \DB::select('SHOW CREATE TABLE ' . $tableName);
                $sqlScript .= $createTableStructure[0]->{"Create Table"} . ";\n\n";

                $rows = \DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $columns = array_keys($rowArray);
                    $values = array_values($rowArray);

                    $escapedValues = array_map(function($value) {
                        if (is_null($value)) return 'NULL';
                        return "'" . addslashes($value) . "'";
                    }, $values);

                    $sqlScript .= "INSERT INTO `$tableName` (`" . implode("`, `", $columns) . "`) VALUES (" . implode(", ", $escapedValues) . ");\n";
                }
                $sqlScript .= "\n\n";
            }

            file_put_contents($fullPath, $sqlScript);

            return back()->with('success', 'Respaldo generado con éxito (Modo Compatible): ' . $filename);

        } catch (\Throwable $e) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            return back()->withErrors(['error' => 'Error al generar el respaldo compatible: ' . $e->getMessage()]);
        }
    }

    public function download($filename)
    {
        if (Storage::disk($this->disk)->exists('backups/' . $filename)) {
            return Storage::disk($this->disk)->download('backups/' . $filename);
        }
        return back()->withErrors(['error' => 'El archivo no existe']);
    }

    public function destroy($filename)
    {
        if (Storage::disk($this->disk)->exists('backups/' . $filename)) {
            Storage::disk($this->disk)->delete('backups/' . $filename);
            return back()->with('success', 'Archivo eliminado.');
        }
        return back()->withErrors(['error' => 'No se pudo eliminar el archivo.']);
    }
}