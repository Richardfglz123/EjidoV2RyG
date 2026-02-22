@extends('cpanel.plantilla')
@section('title', 'Dashboard Reparto')

@section('content')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Encabezado Estilo Usuarios (Borde inferior y texto verde) --}}
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-hand-holding-usd me-2"></i> Reparto Utilidad
        </h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <span class="text-muted fw-bold">Monto del año {{ date('Y') }}</span>
        </div>
    </div>

    {{-- SECCIÓN 1: REPARTO UTILIDAD --}}
    <div class="card card-ejidal mb-4">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0" style="font-size: 1.1rem;">
                <i class="fas fa-list-alt me-2"></i> Información de Repartos
            </h3>
            <div class="d-flex gap-2">
                <a href="{{ route('monto.index') }}" class="btn btn-sm btn-light text-ejidal fw-bold">
                    AGREGAR MONTO
                </a>
                <button class="btn btn-sm btn-light text-ejidal fw-bold" id="btn-fijar-fecha">
                    FIJAR FECHA LÍMITE
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md mb-3">
                    <label class="form-label fw-bold text-muted small">FINIQUITO SANEAMIENTO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-ejidal"
                               value="{{ number_format($finiquito_saneamiento->UtilidadAnual ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md mb-3">
                    <label class="form-label fw-bold text-muted small">PRIMER REPARTO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-ejidal"
                               value="{{ number_format($primer_reparto->UtilidadAnual ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md mb-3">
                    <label class="form-label fw-bold text-muted small">SEGUNDO REPARTO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-ejidal"
                               value="{{ number_format($segundo_reparto->UtilidadAnual ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md mb-3">
                    <label class="form-label fw-bold text-muted small">FINIQUITO UTILIDADES</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-ejidal"
                               value="{{ number_format($finiquito_utilidades->UtilidadAnual ?? 0, 2) }}" readonly>
                    </div>
                </div>
                {{-- NUEVO CUADRO AGREGADO --}}
                <div class="col-md mb-3">
                    <label class="form-label fw-bold text-muted small">REPARTO FINIQUITO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-ejidal"
                               value="{{ number_format($reparto_finiquito_nuevo->UtilidadAnual ?? 0, 2) }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- SECCIÓN 2: DESCUENTOS --}}
    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
            <h3 class="card-title m-0" style="font-size: 1.1rem;">
                <i class="fas fa-minus-circle me-2"></i> Descuentos Faenas y Asambleas
            </h3>
            <a href="{{ route('descuento.descuento') }}" class="btn btn-sm btn-light text-ejidal fw-bold">
                AGREGAR MONTO
            </a>
        </div> <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small">DESCUENTO FAENAS DE SANEAMIENTO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-danger"
                               value="{{ number_format($descuento_saneamiento->monto ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small">DESCUENTO FAENAS DE APROVECHAMIENTO</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-danger"
                               value="{{ number_format($descuento_aprovechamiento->monto ?? 0, 2) }}" readonly>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold text-muted small">DESCUENTO ASAMBLEAS</label>
                    <div class="input-group">
                        <span class="input-group-text">$</span>
                        <input type="text" class="form-control bg-light fw-bold text-danger"
                               value="{{ number_format($descuento_asambleas->monto ?? 0, 2) }}" readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                    console.error("Error al obtener fecha:", e);
                    Swal.close();
                }

                Swal.fire({
                    title: 'Fijar Fecha Límite (Primer Reparto)',
                    icon: 'calendar',
                    confirmButtonColor: '#1d5e3a',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save me-1"></i> Guardar Fecha',
                    cancelButtonText: 'Cancelar',
                    html: `
                            <div class="text-start">
                                <p class="mb-2">Selecciona la fecha límite. <br>
                                <b class="text-danger">Después de este día, no se permitirán más acciones.</b></p>
                                <input type="date" id="swal-fecha-limite" class="form-control" value="${fechaActual}">
                            </div>
                        `,
                    preConfirm: () => {
                        const fecha = document.getElementById('swal-fecha-limite').value;
                        if (!fecha) {
                            Swal.showValidationMessage('Por favor, selecciona una fecha');
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
                                Swal.fire('¡Éxito!', 'La fecha límite ha sido guardada.', 'success');
                            } else {
                                Swal.fire('Error', data.message || 'No se pudo guardar.', 'error');
                            }
                        } catch (error) {
                            Swal.fire('Error', 'Error de conexión con el servidor.', 'error');
                        }
                    }
                });
            });
        }
    });
</script>

@endsection