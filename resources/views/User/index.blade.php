@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Bienvenido {{ Auth::user()->name  }}</div>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif


                            <div class="text-center"><h2>Tabla de posiciones</h2></div>
                        <table class="table table-striped table-hover">
                            <thead class="text-center">
                            <tr>
                                <th scope="col"> </th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Puntos</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            @foreach($positions as $position)
                                <tr>
                                    <td>
                                        @switch($position->posicion)
                                            @case('s')
                                                <i class="bi bi-arrow-up" style="color: green"></i>
                                            @break
                                            @case('b')
                                                <i class="bi bi-arrow-down" style="color: red"></i>
                                            @break
                                            @case('i')
                                            @break
                                        @endswitch

                                    </td>
                                    <td>{{$position->name}}</td>
                                    <td>{{$position->points}}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
