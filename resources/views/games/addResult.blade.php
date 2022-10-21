@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            <h2>Juegos</h2>
                        </div>
                        <table class="table table-striped table-hover text-center">
                            <thead>
                                <tr>
                                    <th scope="col">Juego</th>
                                    <th scope="col">Accion</th>
                                    <th scope="col">Fecha</th>
                                    <th scope="col">Hora Inicio</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <form method="get" action="{{ route('initGame') }}">
                                    <tr>
                                        <td>
                                            {{$game->team1}} vrs {{$game->team2}}
                                        </td>
                                        <td>
                                            <input type="text" name="gameId" value="{{$game->id}}" hidden>

                                            @switch($game->status)
                                                @case(1)
                                                    <input type="submit" value="{{$game->statusname}}" class="btn btn-outline-danger">
                                                @break
                                                @case(2)
                                                    <input type="submit" value="{{$game->statusname}}" class="btn btn-outline-primary">
                                                @break
                                            @endswitch

                                            @if(is_numeric($game->score1) && is_numeric($game->score2))
                                                {{ $game->score1 }} - {{ $game->score2 }}
                                            @endif
                                        </td>
                                        <td>{{$game->dateGame}}</td>
                                        <td>{{$game->timeGame}}</td>
                                    </tr>
                                </form>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (Session::has('success'))
                        <div class="alert alert-success">
                            <ul>
                                <li>{{ Session::get('success') }}</li>
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

