@extends('cpanel.plantilla')
@section('title', 'Listado de Entradas')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-file-import me-2"></i> Historial de Entradas de Inventario</span>
            <a href="{{ route('entradas.create') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-plus-circle me-1"></i> Nueva Entrada
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-ejidal">
                    <tr>
                        <th class="ps-3">Artículo</th>
                        <th>Cantidad</th>
                        <th>Fecha</th>
                        <th>Observaciones</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($entradas as $e)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-bold">{{ $e->articulo->descripcion }}</div>
                            </td>
                            <td>
                                <span class="badge bg-success-light text-success px-3">
                                    + {{ $e->Cantidad }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($e->Fecha)->format('d/m/Y') }}</td>
                            <td class="text-muted small">
                                {{ $e->Observaciones ?? 'Sin observaciones' }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group shadow-sm" role="group">
                                    <a href="{{ route('entradas.edit', $e->Id_Entrada) }}"
                                       class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('entradas.destroy', $e->Id_Entrada) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar esta entrada?')">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-box-open fa-3x mb-3 d-block"></i> No hay entradas registradas.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- PIE DE PÁGINA CON REPORTES --}}
        <div class="card-footer bg-light border-top d-flex justify-content-between align-items-center">
            <div>
                <a href="{{ route('entradas.pdf') }}" class="btn btn-outline-danger btn-sm me-2" target="_blank">
                    <i class="fas fa-file-pdf me-1"></i> Generar PDF
                </a>
                <a href="{{ route('entradas.excel') }}" class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-excel me-1"></i> Generar Excel
                </a>
            </div>
        </div>
    </div>
@endsection
