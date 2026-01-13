@extends('cpanel/plantilla')
@section('title','Registro actividad')
@section('content')

    <div class="card-body">
        <form action="{{ route('actividades.store') }}" class="cmxform" id="signupForm" method="post">
            @csrf
            @include('cpanel/Actividades/form')
        </form>
    </div>

@endsection
