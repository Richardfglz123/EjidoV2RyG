@extends('cpanel.plantilla')
@section('title','Mi Perfil')
@section('content')

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card perfil-card shadow-sm border-0">
        <div class="card-header card-header-ejidal py-3">
            <h5 class="mb-0"><i class="fas fa-user-circle me-2"></i>Mi Perfil</h5>
        </div>

        <div class="card-body p-4">
            {{-- Información NO modificable --}}
            <div class="mb-4 p-4 rounded-4 border-0 shadow-sm" style="background-color: #f8f9fa;">
                <div class="row g-4 align-items-center">
                    <div class="col-md-4 border-end-md">
                        <div class="d-flex align-items-center">
                            <div class="icon-box me-3 text-muted">
                                <i class="fas fa-id-card fa-2x"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Nombre Completo</small>
                                <span class="fw-semibold text-dark">{{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }} {{ $usuario->Apellido_Materno }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 border-end-md">
                        <div class="d-flex align-items-center ps-md-3">
                            <div class="icon-box me-3 text-muted">
                                <i class="fas fa-user-tag fa-2x"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Nº Ejidatario</small>
                                <span class="badge bg-white text-secondary border px-3 py-2">{{ $usuario->Num_Ejidatario ?? 'No asignado' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center ps-md-3">
                            <div class="icon-box me-3 text-muted">
                                <i class="fas fa-map-marked-alt fa-2x"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem; letter-spacing: 0.5px;">Parcelas Asignadas</small>
                                <div class="d-flex flex-wrap gap-1 mt-1">
                                    @forelse($parcelas as $parcela)
                                        <span class="badge rounded-pill border text-secondary fw-bold" style="background-color: #ffffff;">
                                            #{{ $parcela->No_Parcela }}
                                        </span>
                                    @empty
                                        <span class="text-muted small italic">Sin parcelas</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-at text-muted"></i></span>
                            <input type="text" name="Usuario" class="form-control border-start-0 ps-0 @error('Usuario') is-invalid @enderror"
                                   value="{{ old('Usuario', $usuario->Usuario) }}" required>
                        </div>
                        @error('Usuario') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="Correo" class="form-control border-start-0 ps-0 @error('Correo') is-invalid @enderror"
                                   value="{{ old('Correo', $usuario->Correo) }}" required>
                        </div>
                        @error('Correo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="Telefono" class="form-control border-start-0 ps-0 @error('Telefono') is-invalid @enderror"
                                   value="{{ old('Telefono', $usuario->Telefono) }}" required maxlength="10"
                                   oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="form-text mt-1">Debe contener 10 dígitos numéricos.</div>
                        @error('Telefono') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Sección de Contraseñas mejorada --}}
                <div class="row border-top pt-4 mt-4">
                    <div class="col-12 mb-3">
                        <h6 class="fw-bold text-dark"><i class="fas fa-lock me-2"></i>Seguridad de la cuenta</h6>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña" id="password" class="form-control @error('Contraseña') is-invalid @enderror" autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'icon1')">
                                <i class="fas fa-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('Contraseña') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña_confirmation" id="password_confirm" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm', 'icon2')">
                                <i class="fas fa-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="alert alert-light border-0 py-2 mb-0" style="background-color: #f1f3f5;">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1 text-primary"></i>
                                <strong>Requisitos:</strong> Mínimo 8 caracteres, incluir una mayúscula y un número.
                            </small>
                        </div>
                    </div>
                </div>

                {{-- Sección de Cuentas Vinculadas --}}
                <div class="row border-top pt-4 mt-4">
                    <div class="col-12 mb-3">
                        <h6 class="fw-bold text-dark"><i class="fas fa-link me-2"></i>Cuentas vinculadas</h6>
                        <p class="text-muted small">Vincula tus redes para iniciar sesión rápidamente sin usar contraseña.</p>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-white shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="fab fa-google fa-2x me-3" style="color: #DB4437;"></i>
                                <div>
                                    <span class="d-block fw-bold small">Google</span>
                                    @if($usuario->google_id)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Vinculado</span>
                                    @else
                                        <span class="text-muted small">No conectado</span>
                                    @endif
                                </div>
                            </div>
                            @if($usuario->google_id)
                                {{-- Botón para desvincular si lo deseas --}}
                                <form action="{{ route('social.unlink', 'google') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Desvincular</button>
                                </form>
                            @else
                                <a href="{{ route('social.redirect', 'google') }}" class="btn btn-sm btn-outline-dark fw-bold">Vincular</a>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="p-3 border rounded-3 d-flex align-items-center justify-content-between bg-white shadow-sm">
                            <div class="d-flex align-items-center">
                                <i class="fab fa-apple fa-2x me-3" style="color: #000000;"></i>
                                <div>
                                    <span class="d-block fw-bold small">Apple ID</span>
                                    @if($usuario->apple_id)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">Vinculado</span>
                                    @else
                                        <span class="text-muted small">No conectado</span>
                                    @endif
                                </div>
                            </div>
                            @if($usuario->apple_id)
                                <form action="{{ route('social.unlink', 'apple') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Desvincular</button>
                                </form>
                            @else
                                <a href="{{ route('social.redirect', 'apple') }}" class="btn btn-sm btn-outline-dark fw-bold">Vincular</a>
                            @endif
                        </div>
                    </div>
                </div>


                <div class="text-end mt-5">
                    <button type="submit" class="btn btn-ejidal px-4 py-2 shadow-sm">
                        <i class="fas fa-save me-2"></i>Actualizar Mi Perfil
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Script para visualizar contraseña --}}
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                passwordInput.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>

@endsection