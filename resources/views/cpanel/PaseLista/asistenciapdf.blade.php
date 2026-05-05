<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #1a4d2e; padding-bottom: 10px; }
        .title { color: #1a4d2e; text-transform: uppercase; margin: 0; }
        .summary { background: #f4f4f4; padding: 10px; margin-bottom: 20px; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #1a4d2e; color: white; padding: 8px; text-align: left; }
        td { border: 1px solid #ddd; padding: 6px; }
        .section-title { background: #e9ecef; padding: 5px 10px; font-weight: bold; border-left: 5px solid #1a4d2e; margin-top: 20px; }
        .text-success { color: #28a745; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
<div class="header">
    <h2 class="title">Control de Asistencia Ejidal</h2>
    <p>Evento: <strong>{{ $sesion->evento->Nombre_Evento }}</strong> | Fecha: {{ $sesion->Fecha }}</p>
</div>

<div class="summary">
    <strong>Resumen General:</strong><br>
    Total Padrón: {{ $total }} |
    Asistentes: <span class="text-success">{{ count($asistieron) }}</span> |
    Ausentes: <span class="text-danger">{{ count($noAsistieron) }}</span>
</div>

<div class="section-title">LISTA DE ASISTENCIA (PRESENTES)</div>
<table>
    <thead>
    <tr>
        <th width="15%">ID</th>
        <th>Nombre del Ejidatario</th>
        <th width="20%">Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($asistieron as $e)
        <tr>
            <td>{{ $e->Id_Ejidatario }}</td>
            <td>{{ $e->Nombre }} {{ $e->Apellido_Paterno }} {{ $e->Apellido_Materno }}</td>
            <td class="text-success">PRESENTE</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="section-title">LISTA DE FALTAS (AUSENTES)</div>
<table>
    <thead>
    <tr>
        <th width="15%">ID</th>
        <th>Nombre del Ejidatario</th>
        <th width="20%">Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($noAsistieron as $e)
        <tr>
            <td>{{ $e->Id_Ejidatario }}</td>
            <td>{{ $e->Nombre }} {{ $e->Apellido_Paterno }} {{ $e->Apellido_Materno }}</td>
            <td class="text-danger">FALTA</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>