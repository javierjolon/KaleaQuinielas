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
                                    <input type="text" name="juegoId" value="{{$game->id}}" hidden>
                                    <input type="text" name="horaJuego" value="{{ $game->horaJuego}}" hidden>
                                    <input type="text" name="fechaJuego" value="{{$game->fechaJuego}}" hidden>

                                @if($game->estatus == 1)
                                    <tr>
                                        <td class="">
                                            <div class="row justify-content-center">
                                                <span style="font-size: 10px;">{{date_format(date_create($game->fechaJuego), 'd-m-Y')}} - {{date_format(date_create($game->horaJuego), 'H:i')}}</span>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    {{$game->equipo1}}
                                                </div>
                                                <div class="col-6">
                                                    {{$game->equipo2}}
                                                </div>
                                            </div>
                                            <div class="row align-items-center">
                                                <div class="col-6">
                                                    <?php $adentro = false; ?>
                                                    @foreach($results as $result)
                                                        @if($result->juegoId == $game->id)
                                                            <?php $adentro = true; ?>
                                                            @if($game->fechaJuego > date('Y-m-d'))
                                                                <input type="number" name="resultadoEquipo1" class="w-50 text-center" min="0" value="{{$result->quinielaEquipo1}}" required>
                                                            @elseif($game->fechaJuego == date('Y-m-d'))
                                                                @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->horaJuego))))
                                                                    <input type="number" name="resultadoEquipo1" class="w-50 text-center" min="0" value="{{$result->quinielaEquipo1}}" required>
                                                                @endif
                                                            @else
                                                                <p style="font-weight: bold">{{$result->quinielaEquipo1}}</p>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                    @if($adentro == false)
                                                        <input type="number" name="resultadoEquipo1" min="0" class="w-50" required>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <?php $adentro = false; ?>
                                                    @foreach($results as $result)
                                                        @if($result->juegoId == $game->id)
                                                            <?php $adentro = true; ?>
                                                            @if($game->fechaJuego > date('Y-m-d')  )
                                                                <input type="number" name="resultadoEquipo2" class="w-50 text-center" min="0" value="{{$result->quinielaEquipo2}}" required>
                                                            @elseif($game->fechaJuego == date('Y-m-d'))
                                                                @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->horaJuego))))
                                                                    <input type="number" name="resultadoEquipo2" class="w-50 text-center" min="0" value="{{$result->quinielaEquipo2}}" required>
                                                                @endif
                                                            @else
                                                                <p style="font-weight: bold">{{$result->quinielaEquipo2}}</p>
                                                            @endif
                                                        @endif
                                                    @endforeach
                                                    @if($adentro == false)
                                                        <input type="number" name="resultadoEquipo2" min="0" class="w-50" required>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td style="vertical-align: middle">
                                            <div class="">
                                                @if($game->fechaJuego > date('Y-m-d'))
                                                    <input type="submit" value="Guardar" class="btn btn-outline-success">
                                                @elseif($game->fechaJuego = date('Y-m-d'))
                                                    @if( date('H:i:s') <= Date("H:i", strtotime("-5 minutes", strtotime($game->horaJuego))))
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

