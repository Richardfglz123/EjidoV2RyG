@extends('cpanel/plantilla')
@section('title','Registro usuario')
@section('content')

    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 text-ejidal">
            <i class="fas fa-users me-2"></i> Usuarios
        </h1>
    </div>

    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-user-plus me-2"></i> Nuevo usuario
        </div>

        <form action="{{ route('Usuarios.store') }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Nombre(s)</label>
                        <input type="text" name="Nombres"
                               class="form-control @error('Nombres') is-invalid @enderror"
                               value="{{ old('Nombres') }}" required>
                        @error('Nombres')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Paterno</label>
                        <input type="text" name="Apellido_Paterno"
                               class="form-control @error('Apellido_Paterno') is-invalid @enderror"
                               value="{{ old('Apellido_Paterno') }}">
                        @error('Apellido_Paterno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Apellido Materno</label>
                        <input type="text" name="Apellido_Materno"
                               class="form-control @error('Apellido_Materno') is-invalid @enderror"
                               value="{{ old('Apellido_Materno') }}">
                        @error('Apellido_Materno')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label>Usuario</label>
                        <input type="text" name="Usuario"
                               class="form-control @error('Usuario') is-invalid @enderror"
                               value="{{ old('Usuario') }}" required>
                        @error('Usuario')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Nombre único para acceder al sistema.</small>
                    </div>

                    <div class="col-md-4" style="position: relative;">
                        <label>Correo</label>
                        <input type="email" name="Correo" id="emailInput"
                               class="form-control @error('Correo') is-invalid @enderror"
                               value="{{ old('Correo') }}" required
                               placeholder="ejemplo@correo.com"
                               autocomplete="off">

                        <div id="emailSuggestions" class="list-group" style="position: absolute; z-index: 1050; width: 92%; display: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        </div>

                        @error('Correo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label>Teléfono</label>
                        <input type="text" name="Telefono"
                               class="form-control @error('Telefono') is-invalid @enderror"
                               value="{{ old('Telefono') }}"
                               required
                               maxlength="10"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                               placeholder="10 dígitos">
                        @error('Telefono')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Escribe tu numero telefonico</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña" id="pass1"
                                   class="form-control @error('Contraseña') is-invalid @enderror"
                                   required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass1', 'icon1')">
                                <i class="fas fa-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('Contraseña')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">Mínimo 8 caracteres, 1 mayúscula y 1 número.</small>
                    </div>

                    <div class="col-md-6">
                        <label>Confirmar contraseña</label>
                        <div class="input-group">
                            <input type="password" name="Contraseña_confirmation" id="pass2"
                                   class="form-control" required>
                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('pass2', 'icon2')">
                                <i class="fas fa-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('Usuarios.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-ejidal">
                        <i class="fas fa-save me-1"></i> Guardar Usuario
                    </button>
                </div>

            </div>
        </form>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        const emailInput = document.getElementById('emailInput');
        const suggestionsContainer = document.getElementById('emailSuggestions');
        const domains = ['gmail.com', 'outlook.com', 'icloud.com', 'yahoo.com', 'hotmail.com'];

        emailInput.addEventListener('input', function() {
            const value = this.value;
            const atIndex = value.indexOf('@');

            if (atIndex === -1) {
                suggestionsContainer.style.display = 'none';
                return;
            }

            const username = value.substring(0, atIndex);
            const domainPart = value.substring(atIndex + 1);

            let suggestionsHtml = '';
            const filteredDomains = domains.filter(d => d.startsWith(domainPart));

            if (filteredDomains.length > 0 && username.length > 0) {
                filteredDomains.forEach(domain => {
                    suggestionsHtml += `
                        <button type="button" class="list-group-item list-group-item-action py-1" onclick="selectEmail('${username}@${domain}')">
                            ${username}@<strong>${domain}</strong>
                        </button>`;
                });
                suggestionsContainer.innerHTML = suggestionsHtml;
                suggestionsContainer.style.display = 'block';
            } else {
                suggestionsContainer.style.display = 'none';
            }
        });

        window.selectEmail = function(fullEmail) {
            emailInput.value = fullEmail;
            suggestionsContainer.style.display = 'none';
            emailInput.focus();
        };

        document.addEventListener('click', function(e) {
            if (e.target !== emailInput && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    </script>

@endsection