@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <div class="text-center">
                            <h2>Tabla de posiciones</h2>

                            @if($cantidad == 1)
                                <span>En juego</span>
                                <div class="spinner-grow spinner-grow-sm" role="status" style="color: red">
                                    <span class="visually-hidden"></span>
                                </div>
                            @endif
                        </div>

                        <table class="table table-striped table-hover">
                            <thead class="text-center">
                            <tr>
                                <th scope="col"> </th>
                                <th scope="col">Nombre</th>
                                <th scope="col">Puntos</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            @foreach($positions as $position)
                                <tr>
                                    <td>

                                        @switch($position->upDownTemp)
                                            @case('s')
                                            <i class="bi bi-arrow-up" style="color: green"></i>
                                            @break
                                            @case('b')
                                            <i class="bi bi-arrow-down" style="color: red"></i>
                                            @break
                                            @case('i')
                                            <i class="bi bi-dash-circle"></i>
                                            @break
                                        @endswitch
                                    </td>
                                    <td class="d-flex flex-row justify-content-end">

                                        <div class="col-3">
                                            @if($position->image == null)
                                                <img style="width: 45px; margin-right: 10px;" src="public/public/img/participantes/nofoto.jpg">
                                            @else
                                                <img style="width: 45px; margin-right: 10px;" class="" src="{{$position->image}}">
                                            @endif
                                        </div>

                                        <div class="col-7">
                                            {{$position->name}}
                                        </div>
                                    </td>
                                    <td>{{$position->accumulatedPointsTemp}}</td>
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
