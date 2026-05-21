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
            $id_referencia = $request->input('id_sesion');
            $qr_data = $request->input('qr_data');

            if (!$id_referencia || !$qr_data) {
                return response()->json(['success' => false, 'message' => "Faltan datos (ID: $id_referencia)"]);
            }

            $sesion = \App\Models\Sesion::firstOrCreate(
                [
                    'Tipo'          => 'Evento',
                    'Id_Referencia' => $id_referencia,
                    'Fecha'         => date('Y-m-d')
                ]
            );

            // 1. LIMPIEZA INTERGELENTE DEL QR
            $raw = strtoupper($qr_data);

            // Reemplazos ortográficos comunes (Normalizamos S, Z, C y acentos)
            $buscar = ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Z', 'S', 'C'];
            $reemplazar = ['A', 'E', 'I', 'O', 'U', 'N', 'S', 'S', 'S'];
            $raw = str_replace($buscar, $reemplazar, $raw);

            // Eliminamos lo que esté entre paréntesis (ej: "(CALANZINGO)")
            $raw = preg_replace('/\([^)]+\)/', '', $raw);

            // Quitamos números, puntos, comas, guiones
            $raw = preg_replace('/[0-9.,-]/', ' ', $raw);

            // Rompemos en palabras, quitamos "HERM" y espacios vacíos
            $palabras = explode(' ', $raw);
            $palabrasLimpias = array_filter($palabras, function($p) {
                $p = trim($p);
                return $p !== '' && $p !== 'HERM' && strlen($p) > 1;
            });

            if (empty($palabrasLimpias)) {
                return response()->json(['success' => false, 'message' => "El QR no contiene un nombre reconocible."]);
            }

            // Esta es la cadena limpia del QR que vamos a emparejar (Ej: "FELIX SUARES OSORIO")
            $cadenaQR = implode(' ', $palabrasLimpias);

            // 2. BUSQUEDA DE CANDIDATOS EN LA BASE DE DATOS
            // Traemos a todos los usuarios que compartan AL MENOS una palabra clave (para no saturar la memoria)
            $query = \Illuminate\Support\Facades\DB::table('Ejidatario as e')
                ->join('usuario as u', 'e.Id_usuario', '=', 'u.Id_usuario');

            // Creamos la misma normalización en la BD (S, Z, C se vuelven S para evitar errores de dedo)
            $concatBD = "REPLACE(REPLACE(REPLACE(UPPER(CONCAT_WS(' ', u.Nombres, u.Apellido_Paterno, u.Apellido_Materno)), 'Z', 'S'), 'C', 'S'), 'Ç', 'S')";

            $query->where(function($q) use ($palabrasLimpias, $concatBD) {
                foreach ($palabrasLimpias as $palabra) {
                    $q->orWhere(\Illuminate\Support\Facades\DB::raw($concatBD), 'LIKE', "%$palabra%");
                }
            });

            $candidatos = $query->select(
                'e.Id_Ejidatario',
                'e.Num_Ejidatario',
                'u.Nombres',
                'u.Apellido_Paterno',
                'u.Apellido_Materno',
                \Illuminate\Support\Facades\DB::raw("$concatBD as nombre_normalizado")
            )->get();

            // 3. EL TRUCO MAESTRO: Encontrar el match más cercano usando la distancia de Levenshtein
            $mejorMatch = null;
            $distanciaMinima = 999; // Buscamos la menor diferencia de letras posible

            foreach ($candidatos as $candidato) {
                // Calculamos cuántas letras de diferencia hay entre el QR y el nombre de la BD
                $distancia = levenshtein($cadenaQR, $candidato->nombre_normalizado);

                // Si es una coincidencia exacta o la más cercana encontrada hasta ahora
                if ($distancia < $distanciaMinima) {
                    $distanciaMinima = $distancia;
                    $mejorMatch = $candidato;
                }
            }

            // Margen de tolerancia: Si el nombre difiere en demasiadas letras, asumimos que no es nadie
            // Un valor de 12 permite pequeños errores ortográficos o segundos nombres ausentes.
            if (!$mejorMatch || $distanciaMinima > 12) {
                return response()->json(['success' => false, 'message' => "No se encontró un usuario con suficiente similitud para: '$cadenaQR'"]);
            }

            $ejidatario = $mejorMatch;

            // 4. REGISTRO DE ASISTENCIA
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
                'nombre' => $ejidatario->Nombres . ' ' . $ejidatario->Apellido_Paterno . ' ' . $ejidatario->Apellido_Materno
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