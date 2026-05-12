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
            {{-- FORMULARIO 1: ACTUALIZACIÓN DE DATOS DEL PERFIL --}}
            <form method="POST" action="{{ route('perfil.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="text-center mb-5">
                    <div class="position-relative d-inline-block">
                        <div class="profile-img-container shadow-sm border border-3 border-white rounded-circle overflow-hidden" style="width: 150px; height: 150px; background-color: #e9ecef;">
                            @if(isset($usuario->foto) && $usuario->foto)
                                <img id="previewImg" src="{{ asset('storage/'.$usuario->foto) }}?v={{ time() }}" alt="Foto de perfil" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <img id="previewImg" src="https://ui-avatars.com/api/?name={{ urlencode($usuario->Nombres.' '.$usuario->Apellido_Paterno) }}&background=6c757d&color=fff&size=150" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <label for="fotoInput" class="btn btn-sm btn-dark position-absolute bottom-0 end-0 rounded-circle shadow" title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </label>
                        <input type="file" name="foto" id="fotoInput" class="d-none" accept=".jpg,.jpeg,.heic">
                    </div>
                    <div class="mt-2 text-muted small">Formatos: <strong>JPG o JPEG</strong></div>
                    @error('foto') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                </div>

                {{-- Información General --}}
                <div class="mb-4 p-4 rounded-4 border-0 shadow-sm" style="background-color: #f8f9fa;">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-4 border-end">
                            <div class="d-flex align-items-center">
                                <div class="icon-box me-3 text-muted"><i class="fas fa-id-card fa-2x"></i></div>
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem;">Nombre Completo</small>
                                    <span class="fw-semibold text-dark">{{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }} {{ $usuario->Apellido_Materno }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 border-end">
                            <div class="d-flex align-items-center ps-md-3">
                                <div class="icon-box me-3 text-muted"><i class="fas fa-user-tag fa-2x"></i></div>
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem;">Nº Ejidatario</small>
                                    <span class="badge bg-white text-secondary border px-3 py-2">{{ $usuario->Num_Ejidatario ?? 'No asignado' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center ps-md-3">
                                <div class="icon-box me-3 text-muted"><i class="fas fa-map-marked-alt fa-2x"></i></div>
                                <div>
                                    <small class="text-uppercase fw-bold text-muted d-block" style="font-size: 0.7rem;">Parcelas</small>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        @forelse($parcelas as $parcela)
                                            <span class="badge rounded-pill border text-secondary fw-bold" style="background-color: #ffffff;">#{{ $parcela->No_Parcela }}</span>
                                        @empty
                                            <span class="text-muted small italic">Sin parcelas</span>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Campos Editables --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">Usuario</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-at text-muted"></i></span>
                            <input type="text" name="Usuario" class="form-control border-start-0 ps-0 @error('Usuario') is-invalid @enderror" value="{{ old('Usuario', $usuario->Usuario) }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold small text-muted text-uppercase">Correo electrónico</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                            <input type="email" name="Correo" class="form-control border-start-0 ps-0 @error('Correo') is-invalid @enderror" value="{{ old('Correo', $usuario->Correo) }}" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-bold small text-muted text-uppercase">Teléfono</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-phone text-muted"></i></span>
                            <input type="text" name="Telefono" class="form-control border-start-0 ps-0 @error('Telefono') is-invalid @enderror" value="{{ old('Telefono', $usuario->Telefono) }}" required maxlength="10">
                        </div>
                    </div>
                </div>

                {{-- Seguridad --}}
                <div class="row border-top pt-4 mt-4">
                    <div class="col-12 mb-3"><h6 class="fw-bold text-dark"><i class="fas fa-lock me-2"></i>Seguridad</h6></div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Nueva contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña" id="password" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password', 'icon1')"><i class="fas fa-eye" id="icon1"></i></button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold small text-muted text-uppercase">Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña_confirmation" id="password_confirm" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password_confirm', 'icon2')"><i class="fas fa-eye" id="icon2"></i></button>
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-ejidal px-4 py-2 shadow-sm">
                        <i class="fas fa-save me-2"></i>Actualizar Mi Perfil
                    </button>
                </div>
            </form> {{-- FIN DEL FORMULARIO DE PERFIL --}}

            <hr class="my-5">

            {{-- SECCIÓN DE CUENTAS VINCULADAS (FUERA DEL FORM PRINCIPAL) --}}
            <div class="row">
                <div class="col-12 mb-3">
                    <h6 class="fw-bold text-dark"><i class="fas fa-link me-2"></i>Cuentas vinculadas</h6>
                    <div class="alert alert-info border-0 shadow-sm mt-2" style="background-color: #e3f2fd; border-radius: 12px;">
                        <div class="d-flex">
                            <i class="fas fa-info-circle me-3 mt-1" style="color: #0288d1; font-size: 1.2rem;"></i>
                            <div>
                                <strong class="d-block" style="color: #01579b; font-size: 0.9rem;">¿Para qué sirve vincular mi cuenta?</strong>
                                <p class="mb-0 small text-secondary" style="line-height: 1.4;">
                                    Al vincular tu cuenta de Google, podrás entrar al sistema de forma más rápida con un solo clic.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <div class="p-3 border rounded-4 d-flex align-items-center justify-content-between bg-white shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fab fa-google fa-2x me-3" style="color: #DB4437;"></i>
                            <div>
                                <span class="d-block fw-bold small">Google</span>
                                <span class="text-muted small">
                                    {{ $usuario->google_id ? 'Cuenta conectada' : 'No conectado' }}
                                </span>
                            </div>
                        </div>

                        @if(!$usuario->google_id)
                            <a href="{{ route('google.redirect') }}"
                               class="btn btn-sm btn-outline-dark fw-bold px-3"
                               style="border-radius: 10px;"
                               onclick="return confirm('Se te redirigirá a Google para vincular tu cuenta. ¿Deseas continuar?');">
                                <i class="fas fa-plus me-1"></i> Vincular
                            </a>
                        @else
                            {{-- FORMULARIO INDEPENDIENTE PARA DESVINCULAR --}}
                            <form action="{{ route('social.unlink', 'google') }}" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas desvincular tu cuenta de Google? Ya no podrás usar el acceso rápido.');" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger fw-bold px-3" style="border-radius: 10px;">
                                    <i class="fas fa-unlink me-1"></i> Desvincular
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            passwordInput.type = passwordInput.type === "password" ? "text" : "password";
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }

        document.getElementById('fotoInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function() { document.getElementById('previewImg').src = reader.result; }
                reader.readAsDataURL(file);
            }
        });
    </script>
@endsection