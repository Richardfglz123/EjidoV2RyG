@extends('cpanel/plantilla')
@section('title','Registro ejidatario')
@section('content')

    <div class="card-body">
        <form action="{{ route('Ejidatarios.store') }}" method="POST" class="cmxform" id="signupForm">
            @csrf
            @include('cpanel/ejidatarios/form')
        </form>
    </div>

@endsection
