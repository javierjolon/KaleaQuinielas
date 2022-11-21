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
                        <li class="nav-item ml-1">
                            <a class="btn btn-outline-secondary active" href="{{route('var')}}">VAR</a>
                        </li>
{{--                        <li class="nav-item">--}}
{{--                            <a class="btn btn-outline-secondary" href="{{route('porPoquito')}}">Tabla por poquito</a>--}}
{{--                        </li>--}}
{{--                        <li class="nav-item">--}}
{{--                            <a class="btn btn-outline-secondary active" href="{{route('nadaQueVer')}}">Tabla nada que ver</a>--}}
{{--                        </li>--}}
                    </ul>

                    <div class="card-body">

                        <div class="text-center"><h4>Resultados de participantes</h4></div>
                        <div class="text-center"><h4></h4></div>

                        <table class="table table-striped table-hover">
                            <thead class="text-center">
                            <tr>
                                <th scope="col">Nombre</th>
                                <th scope="col">Resultado</th>
                                <th scope="col">Ultima modificacion</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            @foreach($respuesta as $item)
                                <tr>
                                    <td>{{$item->name}}</td>
                                    <td>

                                        {{$item->e1}} - {{$item->scoreTeam1}}
                                        <br>
                                        {{$item->e2}} - {{$item->scoreTeam2}}
                                    </td>
                                    <td>
                                        {{$item->fecha}}
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
