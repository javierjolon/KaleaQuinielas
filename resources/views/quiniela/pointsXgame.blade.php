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
                                <th scope="col">Juego</th>
                                <th>Quiniela</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                            <tr class="">
                                <td class="text-left">
                                    <div class="mb-3">
                                        <img class="avatar" src="{{$game->image1}}">
                                        {{$game->team1}} ( {{$game->score1}} )
                                    </div>
                                    <div class="">
                                        <img class="avatar" src="{{$game->image2}}">
                                        {{$game->team2}} ( {{$game->score2}} )
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @foreach($results as $result)
                                            @if($result->gameId == $game->id && $result->userId == Auth::user()->id)
                                                <div class="mb-5">
                                                    <p style="font-weight: bold">{{$result->scoreTeam1}}</p>
                                                </div>
                                            @endif
                                            @if($result->gameId == $game->id && $result->userId == Auth::user()->id)
                                                <div>
                                                    <p style="font-weight: bold">{{$result->scoreTeam2}}</p>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @switch($game->status)
                                            @case(1)
                                                <span>{{$game->nameStatusGame}}</span>
                                            @break
                                            @case(2)
                                                <span>{{$game->nameStatusGame}}</span>
                                                <div class="spinner-grow spinner-grow-sm" role="status" style="color: green">
                                                    <span class="visually-hidden"></span>
                                                </div>
                                            @break
                                            @case(3)
                                                <span style="color: darkred">{{$game->nameStatusGame}}</span>
                                            @break
                                        @endswitch
                                    </div>
                                    <div>
                                        @foreach($results as $result)
                                            @if($result->gameId == $game->id)
                                                @if(is_numeric($result->pointsXGame))
                                                    @if(is_numeric($game->score1) && is_numeric($game->score2))
                                                        <p style="font-weight: bold">Puntos: {{$result->pointsXGame}}</p>
                                                    @endif
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>

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

