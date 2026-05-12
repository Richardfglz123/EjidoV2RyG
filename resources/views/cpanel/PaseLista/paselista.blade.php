@extends('cpanel/plantilla')
@section('title','Pase de Lista')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-list-check me-2"></i> Gestión de Asistencias
        </h1>
    </div>

    {{-- ALERTAS DE ÉXITO --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card card-ejidal shadow-sm mb-4">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-calendar-alt me-2"></i> Selección de Evento para Pase de Lista
        </div>

        <form action="{{ route('asistencia.registrar') }}" method="POST">
            @csrf
            <div class="card-body p-4">
                <div class="row align-items-end">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Selecciona el evento actual:</label>
                        <select name="id_referencia" class="form-select @error('id_referencia') is-invalid @enderror" required>
                            <option value="">-- Seleccionar --</option>
                            @foreach($eventos as $item)
                                <option value="{{ $item->Id_Evento }}" {{ old('id_referencia') == $item->Id_Evento ? 'selected' : '' }}>
                                    {{ $item->Nombre_Evento }}
                                </option>
                            @endforeach
                        </select>
                        @error('id_referencia') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <input type="hidden" name="tipo" value="Evento">
                        <input type="hidden" name="fecha" value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="submit" class="btn btn-ejidal px-5 w-100 mt-3 mt-md-0 shadow-sm">
                            <i class="fas fa-camera me-2"></i> Comenzar pase de lista
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- SECCIÓN: HISTORIAL DE SESIONES --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <span><i class="fas fa-history me-2"></i> Historial de Pases Realizados</span>
            <span class="badge bg-white text-ejidal">{{ count($sesiones) }} Sesiones</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                    <tr>
                        <th class="ps-4 border-0">Fecha</th>
                        <th class="border-0">Evento</th>
                        <th class="text-center border-0">Asistieron</th>
                        <th class="text-center border-0">Ausentes</th>
                        <th class="text-center border-0">Opciones</th>
                        <th class="text-center border-0">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($sesiones as $s)
                        @php
                            $asistieron = $s->asistencias_count ?? 0;
                            $ausentes = max(0, $totalEjidatarios - $asistieron);
                        @endphp
                        <tr>
                            <td class="ps-4 text-muted small">
                                {{ \Carbon\Carbon::parse($s->Fecha)->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="fw-bold text-ejidal">
                                    {{ $s->evento->Nombre_Evento ?? 'Evento Eliminado (#'.$s->Id_Referencia.')' }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-success px-3">{{ $asistieron }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-danger px-3">{{ $ausentes }}</span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('asistencia.excel', $s->Id_Sesion) }}" class="btn btn-sm btn-outline-success" title="Exportar Excel">
                                        <i class="fas fa-file-excel"></i>
                                    </a>
                                    <a href="{{ route('asistencia.pdf', $s->Id_Sesion) }}" class="btn btn-sm btn-outline-danger" title="Exportar PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>
                                </div>
                            </td>
                            <td class="text-center">
                                {{-- BOTÓN PARA ELIMINAR LA SESIÓN DE ASISTENCIA --}}
                                <form action="{{ route('asistencia.destroy', $s->Id_Sesion) }}" method="POST"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este registro de asistencia? No se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                No hay registros de pases de lista anteriores.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection