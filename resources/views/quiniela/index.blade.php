@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <p>Se puede modificar el marcador hasta 5 minutos antes del inicio partido</p>
                        <table class="table table-striped table-hover table-responsive">
                            <thead>
                            <tr>
                                <th scope="col">Equipo 1</th>
                                <th scope="col">Marcador 1</th>
                                <th scope="col">Equipo 2</th>
                                <th scope="col">Marador 2</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Hora Inicio</th>
                                <th scope="col">Accion</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <form method="post" action="{{ route('quiniela') }}">
                                    <input type="text" name="userId" value="" hidden>
                                    <input type="text" name="gameId" value="{{$game->id}}" hidden>
                                    <tr>
                                        <td>{{$game->team1}}</td>
                                        <td>
                                            <input type="number" name="score1" class="w-50">
                                        </td>
                                        <td>{{$game->team2}}</td>
                                        <td>
                                            <input type="number" name="score2" class="w-50">
                                        </td>
                                        <td>{{$game->dateGame}}</td>
                                        <td>{{$game->timeGame}}</td>
                                        <td>
                                            <input type="submit" value="Guardar" class="btn btn-outline-success">
                                        </td>
                                    </tr>
                                </form>

                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
