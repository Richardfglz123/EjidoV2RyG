@extends('cpanel/plantilla')
@section('title','Editar ejidatario')
@section('content')

    <form action="{{ route('Ejidatarios.update', $fila->Id_Ejidatario) }}" method="POST">
        @csrf
        @method('PATCH')
        @include('cpanel/ejidatarios/form')
    </form>

@endsection
