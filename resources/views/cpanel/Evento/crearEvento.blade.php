@extends('cpanel/plantilla')
@section('title','Registro Evento')
@section('content')

    <div class="card-body">
        <form action="{{ route('eventos.store') }}" class="cmxform" id="signupForm" method="post">
            @csrf
            @include('cpanel/Evento/formEvento')
        </form>
    </div>

@endsection