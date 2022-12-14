@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-body">
                        <form method="POST" action="{{route('setTeam')}}">
                            @csrf

                            <div class="input-group mb-3">
                                <input type="text" class="form-control" placeholder="Nombre del equipo" name="team" required>
                                <input type="text" class="form-control" placeholder="Grupo" name="group" required>
                                <button class="btn btn-outline-primary" type="submit">Agregar</button>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">Nombre</th>
                                    <th scope="col">Grupo</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($teams as $team)
                                <tr class="col-12">
                                    <td class="row">
                                        <div class="col-4">
                                            <img class="avatar" src="{{$team->image}}">
                                        </div>
                                        <div class="col">
                                            {{$team->name}}
                                        </div>
                                    </td>
{{--                                    <td>{{$team->name}}</td>--}}
                                    <td class="col-6">{{$team->group}}</td>
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
