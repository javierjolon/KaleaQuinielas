@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{route('setGame')}}">
                            @csrf
                            <div class="input-group mb-3">
                                <label class="input-group-text col-md-2" for="inputGroupSelect01">Equipos</label>
                                <select class="form-select col-md-5" name="team1" required>
                                    <option value="null" selected>Equipo 1</option>
                                    @foreach($teams as $team)
                                        <option value="{{$team->id}}">{{$team->name}}</option>
                                    @endforeach
                                </select>
                                <select class="form-select col-md-5" name="team2" required>
                                    <option value="null" selected>Equipo 2</option>
                                    @foreach($teams as $team)
                                        <option value="{{$team->id}}">{{$team->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="input-group mb-3">
                                <input type="date" class="form-control" name="date" required>
                                <input type="time" class="form-control" name="time" required>
                                <select class="form-select" name="type" required>
                                    <option value="null" selected>Tipo</option>
                                    @foreach($types as $type)
                                        <option value="{{$type->id}}">{{$type->name}}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" type="submit">Agregar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr class="text-center">
                                <th scope="col">Equipo 1</th>
                                <th scope="col"> </th>
                                <th scope="col">Equipo 2</th>
                                <th scope="col">Tipo</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Hora</th>
                            </tr>
                            </thead>
                            <tbody class="text-center">
                            @foreach($games as $game)
                                <tr>
                                    <td>{{$game->team1}}</td>
                                    <td>vs</td>
                                    <td>{{$game->team2}}</td>
                                    <td>{{$game->type}}</td>
                                    <td>{{$game->dateGame}}</td>
                                    <td>{{$game->timeGame}}</td>
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
