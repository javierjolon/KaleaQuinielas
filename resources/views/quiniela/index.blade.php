@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <p>Se puede modificar el marcador hasta 5 minutos antes del inicio partido</p>
                        <table class="table table-striped table-hover table-responsive text-center">
                            <thead>
                            <tr>
                                <th scope="col">Equipo 1</th>
                                <th scope="col">Equipo 2</th>
                                <th scope="col">Accion</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Hora Inicio</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
{{--                                {{dd($games)}}--}}
                                <form method="post" action="{{ route('setQuiniela') }}">
                                    @csrf
                                    <input type="text" name="gameId" value="{{$game->id}}" hidden>
                                    <input type="text" name="timeGame" value="{{$game->timeGame}}" hidden>
                                    <tr>
                                        <td>
                                            {{$game->team1}}
                                            <br>
                                            <?php $adentro = false; ?>
                                            @foreach($results as $result)
                                                @if($result->gameId == $game->id)
                                                    <?php $adentro = true; ?>
                                                    @if($game->dateGame >= date('Y-m-d')  )
                                                        @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                            <input type="number" name="score1" class="w-50 text-center" value="{{$result->scoreTeam1}}">
                                                        @endif
                                                    @else
                                                            <p style="font-weight: bold">{{$result->scoreTeam1}}</p>
                                                    @endif
                                                @endif
                                            @endforeach
                                            @if($adentro == false)
                                                <input type="number" name="score1" class="w-50">
                                            @endif
                                        </td>
                                        <td>
                                            {{$game->team2}}
                                            <br>
                                            <?php $adentro = false; ?>
                                            @foreach($results as $result)
                                                @if($result->gameId == $game->id)
                                                    <?php $adentro = true; ?>
                                                    @if($game->dateGame >= date('Y-m-d')  )
                                                        @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                            <input type="number" name="score1" class="w-50 text-center" value="{{$result->scoreTeam2}}">
                                                        @endif
                                                    @else
                                                        <p style="font-weight: bold">{{$result->scoreTeam2}}</p>
                                                    @endif
                                                @endif
                                            @endforeach
                                            @if($adentro == false)
                                                <input type="number" name="score2" class="w-50">
                                            @endif
                                        </td>
                                        <td>

                                            @if($game->dateGame >= date('Y-m-d')  )
                                                @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->timeGame))))
                                                    <input type="submit" value="Guardar" class="btn btn-outline-success">
                                                @endif
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

