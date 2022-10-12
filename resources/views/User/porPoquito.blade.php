@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <ul class="nav justify-content-center mt-4">
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary" href="{{route('home')}}">Tabla de posiciones</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary active" href="{{route('porPoquito')}}">Tabla por poquito</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-outline-secondary" href="{{route('nadaQueVer')}}">Tabla nada que ver</a>
                        </li>
                    </ul>

                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="text-center"><h4>Personas que por poquito le pegan a los resultados de hoy</h4></div>
                        <div class="text-center"><h4>{{date('d-m-Y')}}</h4></div>

                        <table class="table table-striped table-hover">
                            <thead class="text-center">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Equipo 1</th>
                                <th scope="col">Equipo 2</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            @foreach($games as $game)
                                <tr>
                                    <td>{{$game->name}}</td>
                                    <td>
                                        {{$game->team1}}({{$game->original1}})
                                        <br>
                                        {{$game->score1}}
                                    </td>
                                    <td>
                                        {{$game->team2}}({{$game->original2}})
                                        <br>
                                        {{$game->score2}}
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
