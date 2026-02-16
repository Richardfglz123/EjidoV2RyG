@extends('cpanel.plantilla')
@section('title', 'Listado de Parcelas')
@section('content')

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-map-marked-alt me-2"></i> Listado de Parcelas Registradas</span>
            <a href="{{ route('parcelas.create') }}" class="btn btn-light btn-sm text-dark fw-bold">
                <i class="fas fa-plus-circle me-1"></i> Nueva Parcela
            </a>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-ejidal">
                    <tr>
                        <th class="ps-3" style="width: 150px;">No. Parcela</th>
                        <th>Ubicación / Paraje</th>
                        <th>Ejidatario Asignado</th>
                        <th class="text-center" style="width: 150px;">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($parcelas as $p)
                        <tr>
                            <td class="ps-3">
                                <span class="badge bg-ejidal-light text-black fw-ejidal"></span>
                                {{ $p->noParcela }}
                            </td>
                            <td>
                                <i class="fas fa-location-dot me-1 text-muted"></i>
                                {{ $p->ubicacion ?? 'No especificada' }}
                            </td>
                            <td>
                                @if($p->ejidatario)
                                    <i class="fas fa-user-circle me-1 text-muted"></i> {{ $p->ejidatario }}
                                @else
                                    <span class="text-danger small italic">Sin asignar</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('parcelas.ver', ['noParcela' => $p->noParcela]) }}"
                                       class="btn btn-outline-info btn-sm" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <a href="{{ route('parcelas.editar', $p->Id_Parcela) }}"
                                       class="btn btn-outline-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    <form action="{{ route('parcelas.eliminar', $p->Id_Parcela) }}" method="POST"
                                          style="display:inline;" onsubmit="return confirm('¿Seguro que desea eliminar esta parcela?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-map-signs fa-3x mb-3 d-block"></i>
                                No se encontraron parcelas registradas.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
