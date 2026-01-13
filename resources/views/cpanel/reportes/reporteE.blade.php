<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ejidatarios</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            font-size: 10pt;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid #000;
        }
        .header h1 {
            font-size: 18pt;
            color: #333;
            margin: 5px 0 0 0;
        }
        .header p {
            font-size: 10pt;
            margin: 0;
        }

        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            font-size: 9pt;
            min-width: 400px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.15);
        }

        .styled-table thead tr {
            background-color: #007bff;
            color: #ffffff;
            text-align: left;
        }

        .styled-table th,
        .styled-table td {
            padding: 12px 15px;
            border: 1px solid #dddddd;
        }

        .styled-table tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        .styled-table tbody tr:nth-of-type(even) {
            background-color: #f3f3f3; /* Color para filas pares */
        }

        .styled-table tbody tr:last-of-type {
            border-bottom: 2px solid #007bff;
        }
    </style>
</head>
<body>

<div class="header">
    <h1>Reporte de Ejidatarios</h1>
    <p>Generado el: {{ date('d/m/Y H:i:s') }}</p>
</div>

<table class="styled-table">
    <thead>
    <tr>
        <th>ID</th>
        <th>Número</th>
        <th>Dirección Completa</th> <th>CURP / RFC</th> <th>Acciones</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $fila)
        <tr>
            <td>{{ $fila->Id_Ejidatario }}</td>
            <td>{{ $fila->Num_Ejidatario }}</td>

            <td>
                {{ $fila->Calle }} #{{ $fila->Num_Exterior }},
                Col. {{ $fila->Colonia }}, {{ $fila->Municipio }}
            </td>

            <td>{{ $fila->CURP }} <br> <small>{{ $fila->RFC }}</small></td>

            <td>
                <form action="{{ url('admon/Ejidatarios/' . $fila->Id_Ejidatario) }}"
                      method="POST" style="display:inline-block">
                    @csrf
                    @method('DELETE')
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
