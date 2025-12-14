<table class="table table-striped">
    <thead>
    <tr>
        <th>ID</th>
        <th>Número</th>
        <th>Dirección</th>
        <th>Parcela</th>
    </tr>
    </thead>
    <tbody>
    @foreach($data as $fila)
        <tr>
            <td>{{ $fila->Id_Ejidatario }}</td>
            <td>{{ $fila->Num_Ejidatario }}</td>
            <td>{{ $fila->Direccion }}</td>
            <td>{{ $fila->No_Parcela }}</td>
            <td>
                <form action="{{ url('admon/Ejidatarios/' . $fila->Id_Ejidatario) }}"
                      method="POST" style="display:inline-block">
                </form>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
