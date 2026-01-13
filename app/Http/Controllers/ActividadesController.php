<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActividadesController extends Controller
{
    public function index()
    {
        $actividad = DB::table('Actividad')->get();

        return view('cpanel/Actividades/indexActividad', ['data' => $actividad]);
    }

    public function create()
    {
        return view('cpanel/Actividades/crearEvento');
    }

    public function store(Request $request)
    {
        $request->validate([
            'Tipo' => 'required|string|max:60',
            'Descripcion' => 'required|string|max:200',
            'FechaInicio' => 'required|date',
            'FechaFin' => 'required|date|after_or_equal:FechaInicio',
            'Estado_Actividad' => 'required|string',
            'Registro_Original' => 'required|date',
            'Nueva_Fecha' => 'nullable|string|max:100',
            'Fecha_Realizo' => 'nullable|string|max:100',
        ]);

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
            'Id_Creo' => 'admin',
        ]);

        return redirect()->route('actividades.index');
    }


    public function edit($id){
        $fila = DB::table('Actividad')->where('Id_Actividad', '=', $id)->first();

        return view('cpanel/Actividades/editActividades', ['fila' => $fila]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'Tipo' => 'required|string|max:60',
            'Descripcion' => 'required|string|max:200',
            'FechaInicio' => 'required|date',
            'FechaFin' => 'required|date|after_or_equal:FechaInicio',
            'Estado_Actividad' => 'required|string',
            'Registro_Original' => 'required|date',
            'Nueva_Fecha' => 'nullable|string|max:100',
            'Fecha_Realizo' => 'nullable|string|max:100',
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

        return redirect()->route('actividades.index');
    }

    public function destroy($id){
        DB::table('Actividad')->where('Id_Actividad', '=', $id)->delete();
        return redirect()->route('actividades.index');
    }
}
