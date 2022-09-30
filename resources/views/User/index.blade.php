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
                            <thead>
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Puntos</th>
                            </tr>
                            </thead>
                            <tbody>
{{--                            @foreach($games as $game)--}}
{{--                                <tr>--}}
{{--                                    <td>{{$game->team1}}</td>--}}
{{--                                    <td>{{$game->team2}}</td>--}}
{{--                                    <td>{{$game->dateGame}}</td>--}}
{{--                                    <td>{{$game->timeGame}}</td>--}}
{{--                                </tr>--}}
{{--                            @endforeach--}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
