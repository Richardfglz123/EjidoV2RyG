@extends('cpanel/plantilla')
@section('title','Escaneo QR')
@section('content')

    <style>
        .table tr, .table td, .card {
            transition: none !important;
            transform: none !important;
        }

        #reader video {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            object-fit: cover;
            border-radius: 10px;
        }

        .text-ejidal { color: #198754 !important; font-weight: 700; }
        .card-header-ejidal { background-color: #198754 !important; color: white !important; font-weight: 600; }
        .card-ejidal { border-color: #198754 !important; }

        .new-row {
            animation: highlight 1.5s ease-out;
        }
        @keyframes highlight {
            0% { background-color: #d1e7dd; }
            100% { background-color: transparent; }
        }
    </style>

    <main class="px-md-4 py-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
            <h1 class="h2 text-ejidal text-uppercase">
                <i class="fas fa-qrcode me-2"></i> Pase de Lista: {{ $evento->Nombre_Evento }}
            </h1>
            <a href="{{ route('asistencia.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Finalizar Sesión
            </a>
        </div>

        <div class="row">
            <div class="col-lg-5 mb-4">
                <div class="card shadow-sm card-ejidal">
                    <div class="card-header card-header-ejidal fw-bold">
                        <i class="fas fa-camera me-2"></i> Escáner Activo
                    </div>
                    <div class="card-body bg-light text-center">
                        <div id="reader" style="width: 100%; overflow: hidden; border: 2px solid #dee2e6; border-radius: 10px;"></div>
                        <div id="status-scan" class="mt-3 p-2 rounded bg-white shadow-sm fw-bold text-muted">
                            <i class="fas fa-sync fa-spin me-2"></i> Esperando código QR...
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card shadow-sm card-ejidal">
                    <div class="card-header card-header-ejidal d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-users me-2"></i> Ejidatarios Presentes</span>
                        <span id="contador" class="badge bg-white text-ejidal shadow-sm">0 presentes</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 500px;">
                            <table class="table table-hover align-middle mb-0" id="tablaAsistencia">
                                <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Num_Ejid</th>
                                    <th>Nombre Completo</th>
                                    <th>Hora</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                                </thead>
                                <tbody id="listaCuerpo">
                                @forelse($presentes as $p)
                                    @php
                                        $nombreLimpio = str_ireplace(['\n', "\n", "\r"], ' ', $p->Nombres . ' ' . $p->Apellido_Paterno);
                                    @endphp
                                    <tr id="row-{{ $p->Num_Ejidatario }}">
                                        <td class="ps-3 fw-bold text-muted">#{{ $p->Num_Ejidatario }}</td>
                                        <td class="fw-bold text-dark text-uppercase">{{ $nombreLimpio }}</td>
                                        <td class="small">{{ \Carbon\Carbon::parse($p->Hora)->format('H:i:s') }}</td>
                                        <td class="text-center"><span class="badge bg-success"><i class="fas fa-check"></i> Asisti</span></td>
                                    </tr>
                                @empty
                                    <tr id="vacio">
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            No hay escaneos registrados aún.
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        const html5QrCode = new Html5Qrcode("reader");
        let scanning = true;

        function onScanSuccess(decodedText) {
            if (!scanning) return;
            scanning = false; // Bloqueamos para evitar envíos dobles

            const statusLabel = document.getElementById('status-scan');
            statusLabel.innerHTML = `<b class="text-primary">PROCESANDO...</b>`;

            fetch("{{ route('asistencia.marcar') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    qr_data: decodedText,
                    id_sesion: "{{ $sesion->Id_Sesion }}"
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        actualizarTabla(data.num_ejid, data.nombre);
                        statusLabel.innerHTML = `<span class="text-success">✔ OK: ${data.nombre}</span>`;
                    } else {
                        statusLabel.innerHTML = `<span class="text-danger">✘ ${data.message}</span>`;
                    }
                })
                .catch(err => {
                    statusLabel.innerHTML = `<span class="text-danger">ERROR DE RED</span>`;
                })
                .finally(() => {
                    // Tiempo de espera corto para el siguiente escaneo
                    setTimeout(() => {
                        scanning = true;
                        statusLabel.innerHTML = '<i class="fas fa-sync fa-spin me-2"></i> Esperando QR...';
                    }, 600);
                });
        }

        // CONFIGURACIÓN DE ALTA VELOCIDAD
        const config = {
            fps: 30,             // Más cuadros por segundo = lectura más rápida
            qrbox: (viewfinderWidth, viewfinderHeight) => {
                return { width: viewfinderWidth * 0.8, height: viewfinderHeight * 0.8 }; // Cuadro más grande
            },
            aspectRatio: 1.0,
            disableFlip: true
        };

        // Arrancamos con la cámara trasera por defecto
        html5QrCode.start({ facingMode: "environment" }, config, onScanSuccess);

        function actualizarTabla(id, nombre) {
            if (document.getElementById(`row-${id}`)) return;
            const v = document.getElementById('vacio'); if (v) v.remove();
            const hora = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });

            const row = `
            <tr id="row-${id}" class="new-row">
                <td class="ps-3 fw-bold">#${id}</td>
                <td class="fw-bold text-dark text-uppercase">${nombre}</td>
                <td class="small">${hora}</td>
                <td class="text-center"><span class="badge bg-success"><i class="fas fa-check"></i> ASISTIÓ</span></td>
            </tr>`;
            document.getElementById('listaCuerpo').insertAdjacentHTML('afterbegin', row);

            const n = document.querySelectorAll('#listaCuerpo tr:not(#vacio)').length;
            document.getElementById('contador').innerText = `${n} presentes`;
        }
    </script>
@endsection