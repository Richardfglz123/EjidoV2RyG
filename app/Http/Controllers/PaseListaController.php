<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Evento;
use App\Models\Ejidatario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\AsistenciaExport;
use Maatwebsite\Excel\Facades\Excel;

class PaseListaController extends Controller
{
    public function index()
    {
        $eventos = Evento::leftJoin('Categoria_Evento as c', 'Evento.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->select('Evento.*', 'c.Nombre_Categoria')
            ->get();

        $totalEjidatarios = Ejidatario::count();

        $sesiones = Sesion::with('evento')
            ->leftJoin('Evento as e', 'Sesion.Id_Referencia', '=', 'e.Id_Evento')
            ->leftJoin('Categoria_Evento as c', 'e.Id_Categoria_Evento', '=', 'c.Id_Categoria_Evento')
            ->select('Sesion.*', 'c.Nombre_Categoria')
            ->addSelect([
                'asistencias_count' => DB::table('PaseLista')
                    ->whereColumn('Id_Sesion', 'Sesion.Id_Sesion')
                    ->selectRaw('count(*)')
            ])
            ->orderBy('Sesion.Fecha', 'desc')
            ->get();

        return view('cpanel.PaseLista.paselista', compact('eventos', 'sesiones', 'totalEjidatarios'));
    }

    public function registrarAsistencia(Request $request)
    {
        $request->validate([
            'id_referencia' => 'required',
            'tipo'          => 'required',
            'fecha'         => 'required|date',
        ]);

        $sesion = Sesion::firstOrCreate(
            [
                'Tipo'          => $request->tipo,
                'Id_Referencia' => $request->id_referencia,
                'Fecha'         => $request->fecha
            ]
        );

        $presentes = DB::table('PaseLista as a')
            ->join('Ejidatario as e', 'a.Id_Ejidatario', '=', 'e.Id_Ejidatario')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->where('a.Id_Sesion', $sesion->Id_Sesion)
            ->select(
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                'a.Fecha as Hora'
            )
            ->orderBy('a.Fecha', 'desc')
            ->get();

        $evento = Evento::find($request->id_referencia);

        return view('cpanel.PaseLista.escanear', compact('sesion', 'evento', 'presentes'));
    }

    public function marcarAsistencia(Request $request)
    {
        try {
            // Obtenemos los datos sin importar si vienen por GET o POST
            $id_referencia = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            if (!$id_referencia || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Faltan datos (ID: $id_referencia)"]);
            }

            // Verifica o crea la sesión del día
            $sesion = \App\Models\Sesion::firstOrCreate(
                [
                    'Tipo'          => 'Evento',
                    'Id_Referencia' => $id_referencia,
                    'Fecha'         => date('Y-m-d')
                ]
            );

            // 1. LIMPIEZA DEL QR: Convertimos a mayúsculas y removemos acentos comunes
            $raw = strtoupper($qr_data);
            $buscar = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'];
            $reemplazar = ['A', 'E', 'I', 'O', 'U', 'N'];
            $raw = str_replace($buscar, $reemplazar, $raw);

            // Dejamos únicamente letras de la A a la Z, números y espacios (eliminamos paréntesis, puntos, comas)
            $limpioQR = preg_replace('/[^A-Z0-9 ]/', '', $raw);

            // 2. SEGMENTACIÓN: Rompemos la cadena del QR en palabras individuales
            $palabras = array_filter(explode(' ', $limpioQR));

            // Palabras o anotaciones comunes en tus QR que debemos ignorar para que no estorben en la búsqueda
            $basuraQR = ['HERM', 'CALANZINGO', 'SERGIO', 'FELIX'];

            // 3. FILTRADO: Quitamos números sueltos, letras solas (como la de J.) y palabras basura
            $palabrasFiltradas = array_filter($palabras, function($palabra) use ($basuraQR) {
                return !is_numeric($palabra) && strlen($palabra) > 1 && !in_array($palabra, $basuraQR);
            });

            // Si por alguna razón el filtro se queda vacío, respaldamos con las palabras originales limpias
            if (empty($palabrasFiltradas)) {
                $palabrasFiltradas = array_filter($palabras, function($palabra) {
                    return !is_numeric($palabra);
                });
            }

            // 4. CONSULTA DINÁMICA: Construimos la búsqueda en la base de datos
            $query = \Illuminate\Support\Facades\DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario');

            // Creamos un contenedor de texto completo con los campos del usuario para buscar en él
            $concatColumn = \Illuminate\Support\Facades\DB::raw("UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno))");

            // Cada palabra clave filtrada (como 'JULIA', 'CABALLERO', 'PEREZ') se vuelve una condición obligatoria
            foreach ($palabrasFiltradas as $palabra) {
                $query->where($concatColumn, 'LIKE', "%$palabra%");
            }

            $ejidatario = $query->select('e.Id_Ejidatario', 'e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno')
                ->first();

            // Si no se encuentra a pesar de la flexibilidad, devolvemos el error de diagnóstico
            if (!$ejidatario) {
                $diagnosticoBusqueda = implode(' + ', $palabrasFiltradas);
                return response()->json(['success' => false, 'message' => "No hallado buscando: [$diagnosticoBusqueda]"]);
            }

            // 5. REGISTRO: Insertamos o actualizamos la asistencia
            \Illuminate\Support\Facades\DB::table('PaseLista')->updateOrInsert(
                [
                    'Id_Sesion' => $sesion->Id_Sesion,
                    'Id_Ejidatario' => $ejidatario->Id_Ejidatario
                ],
                [
                    'Asistencia' => 1,
                    'Fecha' => now(),
                    'Id_Actividad' => ($sesion->Tipo === 'Actividad') ? $sesion->Id_Referencia : null
                ]
            );

            return response()->json([
                'success' => true,
                'num_ejid' => (int)$ejidatario->Num_Ejidatario,
                'nombre' => $ejidatario->Nombres . ' ' . $ejidatario->Apellido_Paterno
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ]);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            DB::table('PaseLista')->where('Id_Sesion', $id)->delete();
            Sesion::destroy($id);
            DB::commit();
            return redirect()->back()->with('success', 'Sesión y asistencias eliminadas.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Error al eliminar la sesión.');
        }
    }

    public function exportarPdf($id)
    {
        $sesion = Sesion::with('evento')->findOrFail($id);

        $asistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->join('PaseLista as p', 'e.Id_Ejidatario', '=', 'p.Id_Ejidatario')
            ->where('p.Id_Sesion', $id)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        $idsAsistentes = DB::table('PaseLista')->where('Id_Sesion', $id)->pluck('Id_Ejidatario');

        $noAsistieron = DB::table('Ejidatario as e')
            ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario')
            ->whereNotIn('e.Id_Ejidatario', $idsAsistentes)
            ->select('e.Num_Ejidatario', 'u.Nombres', 'u.Apellido_Paterno', 'u.Apellido_Materno')
            ->get();

        $total = Ejidatario::count();

        $pdf = Pdf::loadView('cpanel.PaseLista.asistenciapdf', compact('sesion', 'asistieron', 'noAsistieron', 'total'));
        return $pdf->stream('Reporte_Asistencia_'.$id.'.pdf');
    }
    public function exportarExcel($id)
    {

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\AsistenciaExport($id),
            'Reporte_Asistencia_' . $id . '.xlsx'
        );
    }
}