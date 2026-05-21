<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ActividadesExport;

class ActividadesController extends Controller
{
    /**
     * MÉTODO DE SEGURIDAD
     * Centraliza la validación de permisos y el bypass de administrador.
     */
    private function checkPermission($permission)
    {
        $sesion = session('usuario', session('2fa_user', []));
        $permisos = $sesion['permisos'] ?? [];
        $rol = strtolower(trim($sesion['rol'] ?? ''));

        // Bypass para Administrador (Rol nombre o ID 2)
        if ($rol === 'administrador' || ($sesion['id_rol'] ?? null) == 2) {
            return true;
        }

        if (!in_array($permission, $permisos)) {
            abort(403, 'No tienes permiso para gestionar actividades.');
        }
    }

    public function index()
    {
        $actividad = DB::table('Actividad')
            ->orderBy('Id_Actividad', 'desc')
            ->get();
        return view('cpanel/Actividades/indexActividad', ['data' => $actividad]);
    }

    /**
     * IMPORTANTE: Esta ruta debe ser evaluada ANTES que /{actividade} en web.php
     */
    public function create()
    {
        $this->checkPermission('actividades_crear');
        return view('cpanel/Actividades/crearEvento');
    }

    public function store(Request $request)
    {
        $this->checkPermission('actividades_crear');

        $rules = [
            'Tipo' => 'required|string|max:60',
            'Descripcion' => 'required|string|max:200',
            'FechaInicio' => 'required|date',
            'FechaFin' => 'required|date|after_or_equal:FechaInicio',
            'Estado_Actividad' => 'required|string',
            'Registro_Original' => 'required|date',
        ];

        $messages = [];

        //Validacion de fechas para Faena
        if ($request->Tipo == 'Faena') {

            $existeFaena = DB::table('Actividad')
                ->where('Tipo', 'Faena')
                ->where('FechaInicio', $request->FechaInicio)
                ->exists();

            if ($existeFaena) {

                return back()
                    ->withErrors([
                        'FechaInicio' => 'Ya existe una faena registrada en esa fecha.'
                    ])
                    ->withInput();
            }
        }

        $request->validate($rules, $messages);

        $sesion = session('usuario', session('2fa_user'));

        DB::table('Actividad')->insert([
            'Tipo' => $request->Tipo,
            'Descripcion' => $request->Descripcion,
            'FechaInicio' => $request->FechaInicio,
            'FechaFin' => $request->FechaFin,
            'Estado_Actividad' => $request->Estado_Actividad,
            'Registro_Original' => $request->Registro_Original,
            'Nueva_Fecha' => $request->Nueva_Fecha,
            'Fecha_Realizo' => $request->Fecha_Realizo,
            'Fecha_Creo' => now(),
            'Id_Creo' => $sesion['nombre_completo'] ?? 'Admin',
        ]);

        return redirect()->route('actividades.index')
            ->with('success', 'Actividad creada correctamente.');
    }

    /**
     * MÉTODO SHOW: Agregado para evitar el error BadMethodCallException
     */
    public function show($id)
    {
        $fila = DB::table('Actividad')->where('Id_Actividad', $id)->first();

        // Si no encuentras la actividad, lanzas un 404
        abort_if(!$fila, 404);

        return view('cpanel/Actividades/showActividad', compact('fila'));
    }

    public function edit($id)
    {
        $this->checkPermission('actividades_editar');
        $fila = DB::table('Actividad')->where('Id_Actividad', '=', $id)->first();
        abort_if(!$fila, 404);

        return view('cpanel/Actividades/editActividades', ['fila' => $fila]);
    }

    public function update(Request $request, $id)
    {
        $this->checkPermission('actividades_editar');

        $request->validate([
            'Tipo' => 'required|string|max:60',
            'Descripcion' => 'required|string|max:200',
            'FechaInicio' => 'required|date',
            'FechaFin' => 'required|date|after_or_equal:FechaInicio',
            'Estado_Actividad' => 'required|string',
        ]);

        DB::table('Actividad')
            ->where('Id_Actividad', $id)
            ->update([
                'Tipo' => $request->Tipo,
                'Descripcion' => $request->Descripcion,
                'FechaInicio' => $request->FechaInicio,
                'FechaFin' => $request->FechaFin,
                'Estado_Actividad' => $request->Estado_Actividad,
                'Registro_Original' => $request->Registro_Original,
                'Nueva_Fecha' => $request->Nueva_Fecha,
                'Fecha_Realizo' => $request->Fecha_Realizo,
                'Fecha_Modificado' => now(),
            ]);

        return redirect()->route('actividades.index')->with('success', 'Actividad actualizada.');
    }

    public function destroy($id)
    {
        $this->checkPermission('actividades_eliminar');
        DB::table('Actividad')->where('Id_Actividad', '=', $id)->delete();
        return redirect()->route('actividades.index')->with('success', 'Actividad eliminada.');
    }

    private function filtrar(Request $request)
    {
        $q = DB::table('Actividad');
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $q->whereBetween('FechaInicio', [$request->fecha_inicio, $request->fecha_fin]);
        }
        return $q->get();
    }

    public function reportePDF(Request $request)
    {
        $data = $this->filtrar($request);
        $pdf = Pdf::loadView('cpanel/reportes/reporteActividades', compact('data'));
        return $pdf->stream('Reporte_Actividades.pdf');
    }

    public function reporteExcel(Request $request)
    {
        return Excel::download(new ActividadesExport($request), 'Actividades.xlsx');
    }
}