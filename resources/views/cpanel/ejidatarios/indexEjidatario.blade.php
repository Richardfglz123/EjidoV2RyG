@extends('cpanel/plantilla')
@section('title','Ejidatarios')
@section('content')

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal d-flex justify-content-between">
        <span>
            <i class="fas fa-list me-2"></i> Ejidatarios Registrados
        </span>
            <a href="{{ route('Ejidatarios.create') }}" class="btn btn-sm btn-ejidal">
                <i class="fas fa-plus me-1"></i> Nuevo
            </a>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Ejidatario</th>
                    <th>Dirección</th>
                    <th>Responsable</th>
                    <th>Estatus</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    <tr>
                        <td>{{ $fila->Num_Ejidatario }}</td>

                        <td>
                            CURP: {{ $fila->CURP }} <br>
                            RFC: {{ $fila->RFC }}
                        </td>

                        <td>
                            {{ $fila->Calle }} #{{ $fila->Num_Exterior }}
                            @if($fila->Num_Interior)
                                Int. {{ $fila->Num_Interior }}
                            @endif
                            <br>
                            {{ $fila->Colonia }},
                            {{ $fila->Municipio }},
                            {{ $fila->Estado }}
                        </td>

                        <td>
                            {{ $fila->Nombres }} {{ $fila->Apellido_Paterno }}
                        </td>

                        <td>
                        <span class="badge bg-primary">
                            {{ $fila->NombreEstatus }}
                        </span>
                        </td>

                        <td class="text-center">
                            <a href="{{ route('Ejidatarios.edit', $fila->Id_Ejidatario) }}"
                               class="btn btn-sm btn-outline-success me-1" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('Ejidatarios.destroy', $fila->Id_Ejidatario) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('¿Eliminar ejidatario?')" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-3">
            <a href="{{ route('reportes.ejidatarios.pdf') }}" class="btn btn-primary" target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Generar PDF
            </a>
            <a href="{{ route('reportes.ejidatarios.excel') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> Descargar Excel
            </a>
        </div>
    </div>

@endsection
