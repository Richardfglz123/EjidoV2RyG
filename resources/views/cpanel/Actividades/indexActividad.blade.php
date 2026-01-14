@extends('cpanel/plantilla')
@section('title','Actividades')
@section('content')

    <!-- FILTROS Y REPORTES -->
    <div class="card card-ejidal mb-3">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-filter me-2"></i> Filtros de Reporte
        </div>
        <div class="card-body">
            <form id="filtrosForm" class="row g-2">
                <div class="col-md-3">
                    <label class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Mes</label>
                    <input type="number" name="mes" class="form-control" placeholder="1-12">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Año</label>
                    <input type="number" name="anio" class="form-control" placeholder="2026">
                </div>

                <div class="col-md-2 d-grid align-items-end">
                    <button type="button" id="pdfBtn" class="btn btn-danger mb-1">
                        <i class="fas fa-file-pdf me-1"></i> PDF
                    </button>
                    <button type="button" id="excelBtn" class="btn btn-success">
                        <i class="fas fa-file-excel me-1"></i> Excel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- TABLA DE REGISTROS -->
    <div class="card card-ejidal">
        <div class="card-header card-header-ejidal">
            <i class="fas fa-list me-2"></i> Actividades Registradas
        </div>
        <div class="card-body table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Descripción</th>
                    <th>Fecha de inicio</th>
                    <th>Fecha de fin</th>
                    <th>Estado</th>
                    <th>Fecha de registro</th>
                    <th>Fecha nueva</th>
                    <th>Fecha de realización</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                @foreach($data as $fila)
                    <tr>
                        <td>{{ $fila->Tipo }}</td>
                        <td>{{ $fila->Descripcion }}</td>
                        <td>{{ $fila->FechaInicio }}</td>
                        <td>{{ $fila->FechaFin }}</td>
                        <td>{{ $fila->Estado_Actividad }}</td>
                        <td>{{ $fila->Registro_Original }}</td>
                        <td>{{ $fila->Nueva_Fecha }}</td>
                        <td>{{ $fila->Fecha_Realizo }}</td>
                        <td>
                            <a href="{{ route('actividades.edit', $fila->Id_Actividad) }}"
                               class="btn btn-sm btn-outline-success me-1"
                               title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('actividades.destroy', $fila->Id_Actividad) }}"
                                  method="POST"
                                  style="display:inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Eliminar"
                                        onclick="return confirm('¿Estás seguro que deseas eliminar esta actividad?')">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script>
        const form = document.getElementById('filtrosForm');

        document.getElementById('pdfBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.open("{{ route('actividades.reporte.pdf') }}?" + params, "_blank");
        };

        document.getElementById('excelBtn').onclick = () => {
            const params = new URLSearchParams(new FormData(form)).toString();
            window.location = "{{ route('actividades.reporte.excel') }}?" + params;
        };
    </script>

@endsection
