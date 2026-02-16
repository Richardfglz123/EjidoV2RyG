<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class RespaldoController extends Controller
{
    public function index()
    {
        // Asegurarse que la carpeta existe
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }

        $files = Storage::files('backups');
        $respaldos = [];

        foreach ($files as $file) {
            $respaldos[] = [
                'nombre' => basename($file),
                'tamaño' => round(Storage::size($file) / 1024 / 1024, 2) . ' MB',
                'fecha'  => date('d/m/Y H:i:s', Storage::lastModified($file))
            ];
        }

        // Ordenar por fecha (más reciente arriba)
        usort($respaldos, fn($a, $b) => strtotime($b['fecha']) <=> strtotime($a['fecha']));

        return view('cpanel.Respaldo.respaldo', compact('respaldos'));
    }

    public function store()
    {
        // 1. Nombre y Ruta (Asegurar que la carpeta existe)
        $filename = "respaldo_" . date('Y-m-d_H-i-s') . ".sql";
        $backupPath = storage_path('app/backups');

        if (!file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $fullPath = $backupPath . DIRECTORY_SEPARATOR . $filename;

        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbUser = env('DB_USERNAME', 'root');
        $dbPass = env('DB_PASSWORD', '');
        $dbName = env('DB_DATABASE');

        $auth = "--user={$dbUser} " . (!empty($dbPass) ? "--password={$dbPass} " : "");
        $command = "mysqldump --host={$dbHost} {$auth} {$dbName} > \"{$fullPath}\" 2>&1";

        $output = [];
        $resultCode = null;
        exec($command, $output, $resultCode);

        if ($resultCode === 0) {
            return back()->with('success', 'Respaldo generado: ' . $filename);
        } else {
            $errorDetail = implode(' ', $output);
            return back()->withErrors(['error' => 'Error en consola: ' . $errorDetail]);
        }
    }

    public function download($filename)
    {
        $path = 'backups/' . $filename;
        if (Storage::exists($path)) {
            return Storage::download($path);
        }
        return back()->withErrors(['error' => 'El archivo no existe.']);
    }

    public function destroy($filename)
    {
        $path = 'backups/' . $filename;
        if (Storage::exists($path)) {
            Storage::delete($path);
            return back()->with('success', 'Archivo eliminado.');
        }
        return back()->withErrors(['error' => 'No se pudo eliminar el archivo.']);
    }
}