@extends('cpanel/plantilla')
@section('title','Registro costo de multas')
@section('content')

    <div class="card-body">
        <form action="{{ route('multas.store') }}" class="cmxform" id="signupForm" method="post">
            @csrf
            @include('cpanel/Multa/formMulta')
        </form>
    </div>

@endsection