@extends('cpanel/plantilla')
@section('title','Editar actividades')
@section('content')

    <form action="{{url('admon/actividades/'.$fila->Id_Actividad)}}" method="post">
        @csrf
        {{method_field('PATCH')}}
        @include('cpanel/Actividades/form');
    </form>

@endsection
