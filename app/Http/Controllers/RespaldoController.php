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
            file_exists('/opt/homebrew/bin/mysqldump') => '/opt/homebrew/bin/mysqldump',
            file_exists('/usr/local/bin/mysqldump')    => '/usr/local/bin/mysqldump',
            default                                    => 'mysqldump',
        };

        $passArg = !empty($dbPass) ? "--password=" . escapeshellarg($dbPass) : "";
        $command = sprintf(
            '%s --host=%s --user=%s %s %s > %s 2>&1',
            $mysqldump,
            escapeshellarg($dbHost),
            escapeshellarg($dbUser),
            $passArg,
            escapeshellarg($dbName),
            escapeshellarg($fullPath)
        );

        $output = [];
        $resultCode = null;
        exec($command, $output, $resultCode);

        // 5. Verificación ¿Existe el archivo y pesa más de 0 bytes?
        if ($resultCode === 0 && Storage::disk($this->disk)->exists('backups/' . $filename) && Storage::disk($this->disk)->size('backups/' . $filename) > 0) {
            return back()->with('success', 'Respaldo generado: ' . $filename);
        } else {
            $errorDetail = implode(' ', $output);
            // Si el archivo se creo pero está vacío o hubo error se borra
            if (Storage::disk($this->disk)->exists('backups/' . $filename)) {
                Storage::disk($this->disk)->delete('backups/' . $filename);
            }
            return back()->withErrors(['error' => 'Error al generar: ' . ($errorDetail ?: 'El archivo quedó vacío o no se creó.')]);
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