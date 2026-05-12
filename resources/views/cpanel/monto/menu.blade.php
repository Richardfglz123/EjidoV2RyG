@extends('cpanel.plantilla')
@section('title', 'Dashboard Reparto')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Encabezado --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Reparto Utilidad
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <span class="text-muted fw-bold">Monto del año {{ date('Y') }}</span>
        </div>
    </div>

    <div class="card card-ejidal mb-4 shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0" style="font-size: 1.1rem;">
                <i class="fas fa-list-alt me-2"></i> Información de Repartos
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('monto.index') }}" class="btn btn-sm btn-light text-ejidal fw-bold">
                    <i class="fas fa-cog me-1"></i> CONFIGURAR MONTOS
                </a>
                <button class="btn btn-sm btn-light text-ejidal fw-bold" id="btn-fijar-fecha">
                    <i class="fas fa-calendar-alt me-1"></i> FIJAR FECHA LÍMITE
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Reparto Finiquito</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold text-ejidal"
                               value="{{ number_format($finiquito_saneamiento->Monto ?? 0, 2) }}" readonly>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Primer Reparto</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold text-ejidal"
                               value="{{ number_format($primer_reparto->Monto ?? 0, 2) }}" readonly>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Segundo Reparto</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold text-ejidal"
                               value="{{ number_format($segundo_reparto->Monto ?? 0, 2) }}" readonly>
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Finiquito Utilidades</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold text-ejidal"
                               value="{{ number_format($finiquito_utilidades->Monto ?? 0, 2) }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SECCIÓN 2: DESCUENTOS --}}
    <div class="card card-ejidal shadow-sm">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0" style="font-size: 1.1rem;">
                <i class="fas fa-minus-circle me-2"></i> Multas Faenas y Asambleas
            </h3>
            <a href="{{ route('descuento.descuento') }}" class="btn btn-sm btn-light fw-bold">
                <i class="fas fa-edit me-1"></i> AJUSTAR DESCUENTOS
            </a>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Saneamiento</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold"
                               value="{{ number_format($descuento_saneamiento->Costo ?? 0, 2) }}" readonly>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Aprovechamiento</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold"
                               value="{{ number_format($descuento_aprovechamiento->Costo ?? 0, 2) }}" readonly>
                    </div>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small text-uppercase">Asambleas</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white fw-bold">$</span>
                        <input type="text" class="form-control bg-white fw-bold"
                               value="{{ number_format($descuento_asambleas->Costo ?? 0, 2) }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnFijarFecha = document.getElementById('btn-fijar-fecha');
            if (btnFijarFecha) {
                btnFijarFecha.addEventListener('click', async () => {
                    Swal.fire({
                        title: 'Cargando...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    let fechaActual = '';
                    try {
                        const response = await fetch("{{ route('reparto.primer.obtenerFecha') }}");
                        const data = await response.json();
                        fechaActual = data.fecha_limite || '';
                        Swal.close();
                    } catch (e) {
                        Swal.close();
                    }

                    Swal.fire({
                        title: 'Fijar Fecha Límite',
                        icon: 'calendar',
                        confirmButtonColor: '#1d5e3a',
                        showCancelButton: true,
                        confirmButtonText: 'Guardar Fecha',
                        cancelButtonText: 'Cancelar',
                        html: `<input type="date" id="swal-fecha-limite" class="form-control" value="${fechaActual}">`,
                        preConfirm: () => {
                            const fecha = document.getElementById('swal-fecha-limite').value;
                            if (!fecha) {
                                Swal.showValidationMessage('Selecciona una fecha');
                                return false;
                            }
                            return fecha;
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            try {
                                const response = await fetch("{{ route('reparto.primer.fijarFecha') }}", {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                    },
                                    body: JSON.stringify({ fecha_limite: result.value })
                                });
                                const data = await response.json();
                                if (data.success) {
                                    Swal.fire('¡Éxito!', 'Fecha guardada.', 'success').then(() => {
                                        location.reload();
                                    });
                                }
                            } catch (error) {
                                Swal.fire('Error', 'Error de conexión.', 'error');
                            }
                        }
                    });
                });
            }
        });
    </script>

@endsection