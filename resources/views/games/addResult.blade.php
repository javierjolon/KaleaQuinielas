@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <p>Agregar el resultado del juego de los 90 minutos</p>
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
                                <form method="post" action="{{ route('setResultGame') }}">
                                    @csrf
                                    <input type="text" name="gameId" value="{{$game->id}}" hidden>
                                    <tr>
                                        <td>
                                            {{$game->team1}}
                                            <br>
{{--                                            @if($game->dateGame >= date('Y-m-d')  )--}}
{{--                                                <input type="number" name="score1" class="w-50 text-center" value="{{$game->score1}}">--}}
{{--                                            @else--}}
{{--                                                <p style="font-weight: bold">{{$game->score1}}</p>--}}
{{--                                            @endif--}}

                                            @if(is_numeric($game->score1))
                                                <p style="font-weight: bold">{{$game->score1}}</p>
                                            @else
                                                <input type="number" name="score1" class="w-50">
                                            @endif
                                        </td>
                                        <td>
                                            {{$game->team2}}
                                            <br>
{{--                                            @if($game->dateGame >= date('Y-m-d')  )--}}
{{--                                                <input type="number" name="score1" class="w-50 text-center" value="{{$game->score2}}">--}}
{{--                                            @else--}}
{{--                                                <p style="font-weight: bold">{{$game->score2}}</p>--}}
{{--                                            @endif--}}

                                            @if(is_numeric($game->score2))
                                                <p style="font-weight: bold">{{$game->score2}}</p>
                                            @else
                                                <input type="number" name="score2" class="w-50">
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_numeric($game->score1) && is_numeric($game->score2))
                                            @else
                                                <input type="submit" value="Guardar" class="btn btn-outline-success">
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

