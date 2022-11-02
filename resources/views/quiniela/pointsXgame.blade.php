@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body table-responsive">
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
                                <th></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                            <tr class="col-12">
                                <td>
                                    <div class="row align-items-center">
                                        <div class="col-5">
                                            <img class="avatar" src="{{$game->image1}}">
                                            <p>{{$game->team1}}</p>
                                        </div>
                                        <div class="col-3">
                                            ( {{$game->score1}} )
                                        </div>
                                        <div class="col-4">
                                                <?php $adentro = true; ?>
                                                @foreach($results as $result)
                                                    @if($result->gameId == $game->id && $result->userId == Auth::user()->id )
                                                        <?php $adentro = false; ?>
                                                        @if( $result->scoreTeam1 >= 0 && $result->scoreTeam2 >= 0)
                                                            <span style="font-weight: bold">{{$result->scoreTeam1}}</span>
                                                        @endif
                                                    @endif
                                                @endforeach
                                                @if($adentro)
                                                    <div>
                                                        <span style="font-size: 10px">Quiniela</span>
                                                        <span style="font-size: 10px">Pendiente</span>
                                                        <br>
                                                        <i class="bi bi-clock"></i>
                                                    </div>
                                                @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center">
                                        <div class="col-5">
                                            <img class="avatar" src="{{$game->image2}}">
                                            <p>{{$game->team2}}</p>
                                        </div>
                                        <div class="col-3">
                                            ( {{$game->score2}} )
                                        </div>
                                        <div class="col-4">
                                                @foreach($results as $result)
                                                    @if($result->gameId == $game->id && $result->userId == Auth::user()->id )
                                                        @if( $result->scoreTeam1 >= 0 && $result->scoreTeam2 >= 0)
                                                            <span style="font-weight: bold">{{$result->scoreTeam2}}</span>
                                                        @endif
                                                    @endif
                                                @endforeach
                                        </div>
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
                                                <div class="spinner-grow spinner-grow-sm" role="status" style="color: red">
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








{{--                                <td class="text-center">--}}
{{--                                    <div class="mb-1 d-flex flex-column align-items-center">--}}
{{--                                        <img class="avatar" src="{{$game->image1}}">--}}
{{--                                        <p>{{$game->team1}}</p>--}}
{{--                                    </div>--}}
{{--                                    <div class="mt-1 d-flex flex-column align-items-center">--}}
{{--                                        <img class="avatar" src="{{$game->image2}}">--}}
{{--                                        <p>{{$game->team2}}</p>--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                                <td class="text-left">--}}
{{--                                    <div class="h-50">--}}
{{--                                        ( {{$game->score1}} )--}}
{{--                                    </div>--}}
{{--                                    <div class="h-50">--}}
{{--                                        ( {{$game->score2}} )--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <div>--}}
{{--                                        <?php $adentro = true; ?>--}}
{{--                                        @foreach($results as $result)--}}
{{--                                            @if($result->gameId == $game->id && $result->userId == Auth::user()->id )--}}
{{--                                                <?php $adentro = false; ?>--}}
{{--                                                @if( $result->scoreTeam1 >= 0 && $result->scoreTeam2 >= 0)--}}
{{--                                                    <div class="mb-5">--}}
{{--                                                        <p style="font-weight: bold">{{$result->scoreTeam1}}</p>--}}
{{--                                                    </div>--}}
{{--                                                    <div>--}}
{{--                                                        <p style="font-weight: bold">{{$result->scoreTeam2}}</p>--}}
{{--                                                    </div>--}}
{{--                                                @endif--}}
{{--                                            @endif--}}
{{--                                        @endforeach--}}
{{--                                        @if($adentro)--}}
{{--                                            <div>--}}
{{--                                                <p>Resultado</p>--}}
{{--                                                <p>no</p>--}}
{{--                                                <p>ingresado</p>--}}
{{--                                            </div>--}}
{{--                                        @endif--}}
{{--                                    </div>--}}
{{--                                </td>--}}
{{--                                <td>--}}
{{--                                    <div>--}}
{{--                                        @switch($game->status)--}}
{{--                                            @case(1)--}}
{{--                                                <span>{{$game->nameStatusGame}}</span>--}}
{{--                                            @break--}}
{{--                                            @case(2)--}}
{{--                                                <span>{{$game->nameStatusGame}}</span>--}}
{{--                                                <div class="spinner-grow spinner-grow-sm" role="status" style="color: red">--}}
{{--                                                    <span class="visually-hidden"></span>--}}
{{--                                                </div>--}}
{{--                                            @break--}}
{{--                                            @case(3)--}}
{{--                                                <span style="color: darkred">{{$game->nameStatusGame}}</span>--}}
{{--                                            @break--}}
{{--                                        @endswitch--}}
{{--                                    </div>--}}
{{--                                    <div>--}}
{{--                                        @foreach($results as $result)--}}
{{--                                            @if($result->gameId == $game->id)--}}
{{--                                                @if(is_numeric($result->pointsXGame))--}}
{{--                                                    @if(is_numeric($game->score1) && is_numeric($game->score2))--}}
{{--                                                        <p style="font-weight: bold">Puntos: {{$result->pointsXGame}}</p>--}}
{{--                                                    @endif--}}
{{--                                                @endif--}}
{{--                                            @endif--}}
{{--                                        @endforeach--}}
{{--                                    </div>--}}

{{--                                </td>--}}
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

