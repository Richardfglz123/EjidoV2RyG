@extends('cpanel/plantilla')
@section('title', 'Análisis de Ejidatarios')
@section('content')
<div class="container mt-4">
    <div class="card shadow">
        
        <div class="card-header bg-dark">
            <h2 class="mb-0 text-white">Análisis Inteligente de Ejidatarios (K-Means Clustering)</h2>
        </div>
        
        <div class="card-body">
            <p class="text-muted">Clasificación automática de usuarios para priorizar la atención administrativa y legal.</p>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>CURP</th>
                            <th>Estatus Original</th>
                            <th>Grupo Asignado</th>
                            <th>Prioridad / Acción Sugerida</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($datos as $fila)
                        <tr>
                            <td>{{ $fila['id'] }}</td>
                            <td>{{ $fila['CURP'] }}</td>
                            <td>
                                
                                <span class="badge badge-info" style="color: #000 !important; background-color: #17a2b8; padding: 8px;">
                                    {{ $fila['estatus'] }}
                                </span>
                            </td>
                            <td class="text-center"><strong>Grupo {{ $fila['cluster'] }}</strong></td>
                            <td>
                                @if($fila['cluster'] == 1)
                                    <span class="badge" style="background-color: #28a745; color: #000 !important; font-weight: bold; padding: 8px; border-radius: 4px;">
                                        <i class="fas fa-check"></i> Operativo / Activo
                                    </span>
                                @elseif($fila['cluster'] == 0)
                                    
                                    <span class="badge" style="background-color: #dc3545; color: #000 !important; font-weight: bold; padding: 8px; border-radius: 4px;">
                                        <i class="fas fa-gavel"></i> Trámites Legales
                                    </span>
                                @else
                                    <span class="badge" style="background-color: #ffc107; color: #000 !important; font-weight: bold; padding: 8px; border-radius: 4px;">
                                        <i class="fas fa-exclamation-triangle"></i> Atención Especial
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer">
            <small class="text-muted">Nota: Los grupos son generados por un análisis de proximidad de datos.</small>
        </div>
    </div>
</div>

{{-- Estilo extra para asegurar que las celdas no hereden el color blanco de la plantilla --}}
<style>
    .table td {
        color: #000 !important; 
        vertical-align: middle;
    }
    .badge {
        font-size: 0.9rem;
        display: inline-block;
    }
</style>
@endsection