@extends('cpanel/plantilla')
@section('title','Registro ejidatario')
@section('content')

    <form action="{{ route('Ejidatarios.store') }}" method="POST">
        @csrf
        @include('cpanel/ejidatarios/form')
    </form>

@endsection
