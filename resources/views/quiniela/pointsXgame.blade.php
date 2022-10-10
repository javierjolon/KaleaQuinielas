@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h3>Puntos por partido</h3>
                        <table class="table table-striped table-hover table-responsive text-center">
                            <thead>
                            <tr>
                                <th scope="col">Equipo 1</th>
                                <th></th>
                                <th scope="col">Equipo 2</th>
                                <th scope="col">Fecha y Hora</th>
                                <th scope="col">Puntos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                            <tr>
                                <td>
                                    {{$game->team1}}
                                    <br>
                                    @foreach($results as $result)
                                        @if($result->gameId == $game->id)
                                            <p style="font-weight: bold">{{$result->scoreTeam1}}</p>
                                        @endif
                                    @endforeach
                                </td>
                                <td>vrs</td>
                                <td>
                                    {{$game->team2}}
                                    <br>
                                    @foreach($results as $result)
                                        @if($result->gameId == $game->id)
                                            <p style="font-weight: bold">{{$result->scoreTeam2}}</p>
                                        @endif
                                    @endforeach
                                </td>
                                <td>{{$game->dateGame}} <br> {{$game->timeGame}} </td>
                                <td>
                                    @foreach($results as $result)
                                        @if($result->gameId == $game->id)
                                            @if(is_numeric($result->pointsXGame))
                                                <p style="font-weight: bold">{{$result->pointsXGame}}</p>
                                                @if(is_numeric($game->score1) && is_numeric($game->score2))
                                                @else
                                                    <p>Pendiente</p>
                                                @endif
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
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

