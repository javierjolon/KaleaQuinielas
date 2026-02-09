@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-8">
                <div class="card">
                    <div class="card-body">
                        <p>Se puede modificar el marcador hasta 5 minutos antes del inicio del partido</p>
                        <table class="table table-striped table-hover table-responsive text-center">
                            <thead>
                            <tr>
                                <th scope="col">Juego</th>
                                <th scope="col">Acción</th>
{{--                                <th scope="col">Fecha</th>--}}
{{--                                <th scope="col">Hora Inicio</th>--}}
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <form method="post" action="{{ route('setQuiniela') }}">
                                    @csrf
                                    <input type="text" name="gameId" value="{{$game->id}}" hidden>
                                    <input type="text" name="timeGame" value="{{ $game->timeGame}}" hidden>
                                    <input type="text" name="dateGame" value="{{$game->dateGame}}" hidden>

                                @if($game->status == 1)
                                    <tr>
                                        <td class="">
                                            <div class="row justify-content-center">
                                                <span style="font-size: 10px;">{{date_format(date_create($game->dateGame), 'd-m-Y')}} - {{date_format(date_create($game->timeGame), 'H:i')}}</span>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    {{$game->team1}}
                                                </div>
                                                <div class="col-6">
                                                    {{$game->team2}}
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    <?php $adentro = false; ?>
                                                    @foreach($results as $result)
                                                        @if($result->gameId == $game->id)
                                                            <?php $adentro = true; ?>
                                                            @if($game->dateGame > date('Y-m-d'))
                                                                <input type="number" name="score1" class="w-50 text-center" min="0" value="{{$result->scoreTeam1}}" required>
                                                            @elseif($game->dateGame == date('Y-m-d'))
                                                                @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                                    <input type="number" name="score1" class="w-50 text-center" min="0" value="{{$result->scoreTeam1}}" required>
                                                                @endif
                                                            @else
                                                                <p style="font-weight: bold">{{$result->scoreTeam1}}</p>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                    @if($adentro == false)
                                                        <input type="number" name="score1" min="0" class="w-50" required>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <?php $adentro = false; ?>
                                                    @foreach($results as $result)
                                                        @if($result->gameId == $game->id)
                                                            <?php $adentro = true; ?>
                                                            @if($game->dateGame > date('Y-m-d')  )
                                                                <input type="number" name="score2" class="w-50 text-center" min="0" value="{{$result->scoreTeam2}}" required>
                                                            @elseif($game->dateGame == date('Y-m-d'))
                                                                @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                                    <input type="number" name="score2" class="w-50 text-center" min="0" value="{{$result->scoreTeam2}}" required>
                                                                @endif
                                                            @else
                                                                <p style="font-weight: bold">{{$result->scoreTeam2}}</p>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                    @if($adentro == false)
                                                        <input type="number" name="score2" min="0" class="w-50" required>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle">
                                            <div class="">
                                                @if($game->dateGame > date('Y-m-d'))
                                                    <input type="submit" value="Guardar" class="btn btn-outline-success">
                                                @elseif($game->dateGame = date('Y-m-d'))
                                                    @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                        <input type="submit" value="Guardar" class="btn btn-outline-success">
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endif
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

