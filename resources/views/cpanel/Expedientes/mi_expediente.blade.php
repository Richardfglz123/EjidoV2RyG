@extends('cpanel.plantilla')
@section('title', 'Mi Expediente')

@section('content')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2 text-ejidal"><i class="fas fa-file-user me-2"></i> Mi Expediente Digital</h1>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm mb-4">
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-user-circle fa-5x text-secondary"></i>
                        </div>
                        <h5 class="fw-bold">{{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }}</h5>
                        <p class="text-muted small">Beneficiario</p>
                        <hr>
                        <div class="text-start small">
                            <p><strong>Usuario:</strong> {{ $usuario->Usuario }}</p>
                            <p><strong>Correo:</strong> {{ $usuario->Correo }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header card-header-ejidal">
                        <i class="fas fa-file-upload me-2"></i> Estado de mis Documentos
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                <tr>
                                    <th>Documento</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $tipos = [
                                        'INE' => ['icon' => 'fa-address-card', 'color' => 'primary', 'input' => 'doc_ine'],
                                        'CURP' => ['icon' => 'fa-file-invoice', 'color' => 'info', 'input' => 'doc_curp'],
                                        'DOMICILIO' => ['icon' => 'fa-home', 'color' => 'success', 'input' => 'doc_comprobante']
                                    ];
                                @endphp

                                @foreach($tipos as $nombre => $info)
                                    <tr>
                                        <td>
                                            <i class="fas {{ $info['icon'] }} me-2 text-{{ $info['color'] }}"></i>
                                            <strong>{{ $nombre }}</strong>
                                        </td>
                                        <td>
                                            @if(isset($docs[$nombre]))
                                                <span class="badge bg-success-subtle text-success border border-success">
                                                <i class="fas fa-check me-1"></i> Cargado
                                            </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning">
                                                <i class="fas fa-clock me-1"></i> Pendiente
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(isset($docs[$nombre]))
                                                <a href="{{ asset($docs[$nombre]) }}" target="_blank" class="btn btn-sm btn-outline-dark">
                                                    <i class="fas fa-eye"></i> Ver
                                                </a>
                                            @else
                                                <form action="{{ route('expedientes.store') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                                                    @csrf
                                                    <input type="hidden" name="id_usuario" value="{{ $usuario->Id_Usuario }}">
                                                    <input type="file" name="{{ $info['input'] }}" class="form-control form-control-sm" accept=".pdf" required>
                                                    <button type="submit" class="btn btn-sm btn-ejidal">Subir</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection