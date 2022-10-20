@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between position-sticky" style="top: 0; background: white">
                            <div>
                                <h4>Puntos por partido</h4>
                            </div>
                            <div>
                                <p>Total puntos: {{$points->points}}</p>
                            </div>
                        </div>

                        <table class="table table-striped table-hover text-center">
                            <thead>
                            <tr>
                                <th scope="col">Equipo 1</th>
                                <th></th>
                                <th scope="col">Equipo 2</th>
{{--                                <th scope="col">Fecha y Hora</th>--}}
                                <th scope="col">Puntos</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                            <tr class="col-12">
                                <td class="row">
                                    <div class="col-4">
                                        <img class="avatar" src="{{$game->image1}}">
                                    </div>
                                    <div class="col-8">
                                        {{$game->team1}} ({{$game->score1}})
                                        <br>
                                        @foreach($results as $result)
                                            @if($result->gameId == $game->id)
                                                <p style="font-weight: bold">{{$result->scoreTeam1}}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>vrs</td>
                                <td class="row">
                                    <div class="col-8">
                                        {{$game->team2}} ({{$game->score2}})
                                        <br>
                                        @foreach($results as $result)
                                            @if($result->gameId == $game->id)
                                                <p style="font-weight: bold">{{$result->scoreTeam2}}</p>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="col-4">
                                        <img class="avatar" src="{{$game->image2}}">
                                    </div>
                                </td>
{{--                                <td>{{$game->dateGame}} <br> {{$game->timeGame}} </td>--}}
                                <td>
                                    @foreach($results as $result)
                                        @if($result->gameId == $game->id)
                                            @if(is_numeric($result->pointsXGame))
                                                @if(is_numeric($game->score1) && is_numeric($game->score2))
                                                    <p style="font-weight: bold">{{$result->pointsXGame}}</p>
                                                @else
                                                    <i class="bi bi-hourglass-split"></i>
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

