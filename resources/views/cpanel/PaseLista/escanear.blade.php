@extends('cpanel/plantilla')
@section('title','Escaneo QR')
@section('content')

    <style>
        #reader video {
            transform: scaleX(1) !important;
            -webkit-transform: scaleX(1) !important;
            object-fit: cover;
        }

        .no-mirror {
            transform: scaleX(1) !important;
        }
    </style>

    <main class="px-md-4 py-4">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
            <h1 class="h2 text-ejidal">
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
                        <div id="reader" style="width: 100%; border-radius: 10px; overflow: hidden;"></div>
                        <div id="status-scan" class="mt-3 fw-bold text-muted">
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
                        <div class="table-responsive" style="max-height: 450px;">
                            <table class="table table-hover align-middle mb-0" id="tablaAsistencia">
                                <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Hora de Entrada</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                                </thead>
                                <tbody id="listaCuerpo">
                                <tr id="vacio">
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        No hay escaneos registrados aún.
                                    </td>
                                </tr>
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
            scanning = false;

            const statusLabel = document.getElementById('status-scan');
            statusLabel.innerHTML = `<span class="text-ejidal">Procesando ID: ${decodedText}...</span>`;

            fetch("{{ route('asistencia.marcar') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    id_ejidatario: decodedText,
                    id_sesion: "{{ $sesion->Id_Sesion }}"
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        actualizarTabla(decodedText, data.nombre);
                        statusLabel.innerHTML = `<span class="text-success"><i class="fas fa-check-circle"></i> ${data.nombre} registrado</span>`;
                    } else {
                        statusLabel.innerHTML = `<span class="text-danger"><i class="fas fa-times-circle"></i> Error: No encontrado</span>`;
                    }
                })
                .catch(() => {
                    statusLabel.innerHTML = `<span class="text-danger">Error de servidor</span>`;
                })
                .finally(() => {
                    setTimeout(() => {
                        scanning = true;
                        statusLabel.innerHTML = `<i class="fas fa-sync fa-spin me-2"></i> Esperando código QR...`;
                    }, 3000);
                });
        }

        function actualizarTabla(id, nombre) {
            const body = document.getElementById('listaCuerpo');
            const vacio = document.getElementById('vacio');
            if (vacio) vacio.remove();

            if (document.getElementById(`row-${id}`)) return;

            const hora = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const row = `
            <tr id="row-${id}" class="table-success border-start border-success border-4">
                <td class="ps-3">#${id}</td>
                <td class="fw-bold text-dark">${nombre}</td>
                <td>${hora}</td>
                <td class="text-center"><span class="badge bg-success">PRESENTE</span></td>
            </tr>`;
            body.insertAdjacentHTML('afterbegin', row);

            const count = document.querySelectorAll('#listaCuerpo tr').length;
            document.getElementById('contador').innerText = `${count} presentes`;
        }

        html5QrCode.start(
            { facingMode: "environment" },
            {
                fps: 10,
                qrbox: 250,
                disableFlip: true
            },
            onScanSuccess
        ).then(() => {
            const videoElement = document.querySelector('#reader video');
            if (videoElement) {
                videoElement.style.transform = "scaleX(1)";
                videoElement.style.webkitTransform = "scaleX(1)";
            }
        }).catch(err => {
            console.error("Error al iniciar cámara: ", err);
        });
    </script>

@endsection