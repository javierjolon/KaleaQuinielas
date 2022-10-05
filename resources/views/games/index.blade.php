@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-body">
                        <form method="POST" action="{{route('setGame')}}">
                            @csrf
                                <select class="form-select" aria-label="Default select example" name="team1" required>
                                    <option value="null" selected>Equipo 1</option>
                                    @foreach($teams as $team)
                                        <option value="{{$team->id}}">{{$team->name}}</option>
                                    @endforeach
                                </select>

                                <select class="form-select" name="team2" required>
                                    <option value="null" selected>Equipo 2</option>
                                    @foreach($teams as $team)
                                        <option value="{{$team->id}}">{{$team->name}}</option>
                                    @endforeach
                                </select>

                            <div class="input-group mb-3">
                                <input type="date" class="form-control" placeholder="Fecha" name="date" required>
                                <input type="time" class="form-control" placeholder="Hora" name="time" required>
                                <button class="btn btn-outline-primary" type="submit">Agregar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th scope="col">Equipo 1</th>
                                <th scope="col">Equipo 2</th>
                                <th scope="col">Fecha</th>
                                <th scope="col">Hora</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($games as $game)
                                <tr>
                                    <td>{{$game->team1}}</td>
                                    <td>{{$game->team2}}</td>
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
