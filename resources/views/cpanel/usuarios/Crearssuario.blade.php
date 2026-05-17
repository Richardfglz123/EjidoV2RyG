@extends('cpanel/plantilla')
@section('title','Registro usuario')
@section('content')

    <div class="card-body">
        <form action="{{ route('Usuarios.store') }}" class="cmxform" id="signupForm" method="post">
            @csrf
            @include('cpanel.usuarios.formusuario')
        </form>
    </div>

@endsection
