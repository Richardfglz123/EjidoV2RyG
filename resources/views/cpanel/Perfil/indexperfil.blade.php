@extends('cpanel.plantilla')
@section('title','Mi Perfil')
@section('content')

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card perfil-card">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user"></i> Mi perfil
        </div>

        <div class="card-body">
            {{-- Información NO modificable --}}
            <div class="mb-4 p-4 rounded-3 border-0 shadow-sm" style="background-color: #f8f9fa;">
                <div class="row align-items-center">
                    <div class="col-md-4 border-end">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-id-card fa-2x text-muted me-3"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Nombre Completo</small>
                                <p class="mb-0 fw-semibold text-dark">
                                    {{ $usuario->Nombres }} {{ $usuario->Apellido_Paterno }} {{ $usuario->Apellido_Materno }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 border-end">
                        <div class="d-flex align-items-center ps-md-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-user-tag fa-2x text-muted me-3"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Nº Ejidatario</small>
                                <p class="mb-0 fw-bold text-secondary">
                                    {{ $usuario->Num_Ejidatario ?? 'No asignado' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="d-flex align-items-center ps-md-3">
                            <div class="flex-shrink-0">
                                <i class="fas fa-map-marked-alt fa-2x text-muted me-3"></i>
                            </div>
                            <div>
                                <small class="text-uppercase fw-bold text-muted" style="font-size: 0.7rem;">Nº Parcela</small>
                                <p class="mb-0 fw-bold text-secondary">
                                    {{ $usuario->No_Parcela ?? 'Sin parcela asignada' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('perfil.update') }}">
                @csrf
                @method('PUT')

                {{-- Campos que si se pueden modificar --}}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Usuario</label>
                        <input type="text" name="Usuario" class="form-control"
                               value="{{ $usuario->Usuario }}" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Correo electronico</label>
                        <input type="email" name="Correo" class="form-control"
                               value="{{ $usuario->Correo }}" required>
                    </div>
                </div>

                <div class="col-md-12 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="text"
                           name="Telefono"
                           class="form-control @error('Telefono') is-invalid @enderror"
                           value="{{ old('Telefono', $usuario->Telefono) }}"
                           required
                           maxlength="10"
                           pattern="\d{10}"
                           title="El teléfono debe tener 10 dígitos numéricos"
                           oninput="this.value = this.value.replace(/[^0-9]/g, '')">

                    @error('Telefono')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Contraseñas --}}
                <div class="row border-top pt-3 mt-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nueva contraseña (opcional)</label>
                        <input type="password" name="Contraseña" class="form-control">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Confirmar contraseña (opcional)</label>
                        <input type="password" name="Contraseña_confirmation" class="form-control">
                    </div>
                </div>

                <div class="text-end mt-4">
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

@endsection
