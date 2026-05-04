@extends('cpanel/plantilla')
@section('title','Multas')
@section('content')

    <main class="px-md-4 py-4">

        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
            <h1 class="h2 text-ejidal">
                <i class="fas fa-list-check me-2"></i> Pase de Lista
            </h1>

            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-file-excel me-1"></i> Exportar Excel
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger">
                        <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                    </button>
                </div>
            </div>
        </div>

        <!-- FORMULARIO CENTRADO -->
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10 col-lg-8"> <!-- Este div es el que controla que no se pegue a los lados -->

                    <div class="card card-ejidal shadow-sm">
                        <div class="card-header card-header-ejidal">
                            <i class="fas fa-calendar-check me-2"></i> Selecciona un evento para iniciar el pase de lista
                        </div>

                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="evento" class="form-label fw-bold text-dark">Selecciona el evento para el pase de lista</label>
                                <select id="evento" name="evento" class="form-select">
                                    <option value="">Seleccionar...</option>
                                    <option value="1">Evento 1</option>
                                    <option value="2">Evento 2</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-secondary px-4">
                                    <i class="fas fa-times me-1"></i> Cancelar
                                </button>
                                <button type="submit" class="btn btn-ejidal px-4">
                                    <i class="fas fa-play me-1"></i> Iniciar pase de lista
                                </button>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

@endsection