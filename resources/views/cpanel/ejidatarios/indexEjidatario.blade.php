@extends('cpanel/plantilla')
@section('title','Ejidatarios')

@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-people-carry me-2"></i> Ejidatarios
        </h1>

        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ url('admon/Ejidatarios/create') }}" class="btn btn-ejidal">
                <i class="fas fa-plus-circle me-1"></i> Nuevo Ejidatario
            </a>
        </div>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-list me-2"></i> Ejidatarios Registrados</span>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Número de Ejidatario</th>
                    <th>Dirección</th>
                    <th>No. Parcela</th>
                    <th class="text-center">Acciones</th>
                </tr>
                </thead>
                <tbody>
                @forelse($data as $fila)
                    <tr>
                        <td>{{ $fila->Num_Ejidatario }}</td>
                        <td>{{ $fila->Direccion }}</td>
                        <td>{{ $fila->No_Parcela }}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <a href="{{ url('admon/ejidatarios/'.$fila->Id_Ejidatario.'/edit') }}"
                                   class="btn btn-warning btn-sm">
                                    <i class="fas fa-edit"></i>
                                </a>

                                <form action="{{ url('admon/ejidatarios/'.$fila->Id_Ejidatario) }}"
                                      method="post" class="d-inline">
                                    @csrf
                                    {{ method_field('DELETE') }}
                                    <button class="btn btn-danger btn-sm"
                                            onclick="return confirm('¿Estas seguro que lo quieres eliminar?')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">
                            No hay ejidatarios registrados.
                        </td>
                    </tr>
                @endforelse
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
