<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            // Solo archivos .sql
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
        $dbHost = config('database.connections.mysql.host') == 'localhost' ? '127.0.0.1' : config('database.connections.mysql.host');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbName = config('database.connections.mysql.database');

        $mysqldump = match (true) {
            file_exists('/usr/bin/mysqldump')          => '/usr/bin/mysqldump',
            file_exists('/opt/homebrew/bin/mysqldump') => '/opt/homebrew/bin/mysqldump',
            file_exists('/usr/local/bin/mysqldump')    => '/usr/local/bin/mysqldump',
            default                                    => 'mysqldump',
        };

        $passArg = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
        $errorLog = storage_path('logs/backup_error.log');

        $command = sprintf(
            '%s --no-defaults --host=%s --user=%s %s %s > %s 2> %s',
            $mysqldump,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $passArg,
            escapeshellarg($dbName),
            escapeshellarg($fullPath),
            escapeshellarg($errorLog)
        );

        $output = [];
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode === 0 && file_exists($fullPath) && filesize($fullPath) > 0) {
            return back()->with('success', 'Respaldo generado con éxito: ' . $filename);
        } else {
            $errorDetail = file_exists($errorLog) ? file_get_contents($errorLog) : 'Error desconocido en la ejecución.';

            if (file_exists($fullPath)) {
                unlink($fullPath);
            }

            return back()->withErrors(['error' => 'Error de MySQL: ' . $errorDetail]);
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