@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Bienvenido {{ Auth::user()->name  }}</div>

                    <div class="card-body text-center">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif


                        <div class="text-center"><h2>Administrador</h2></div>

                        <img class="w-25" src="{{Auth::user()->image}}">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
