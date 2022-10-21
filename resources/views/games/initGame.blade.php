@extends('layouts.admin')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <a href="{{ route('addResult') }}">
                                <i class="bi bi-arrow-left"></i> Atras
                            </a>
                        </div>

                        <div class="text-center">
                            <p>Agregar el resultado del juego de los 90 minutos</p>
                        </div>

                        <div class="d-flex justify-content-between mt-5">
                            @if($games->status == 1)
                                <div>
                                    <form method="post" action="{{ route('starGame') }}">
                                        @csrf
                                        <input type="text" name="gameId" value="{{$games->id}}" hidden>
                                        <input type="submit" value="Iniciar Partido" class="btn btn-outline-success">
                                    </form>
                                </div>
                            @endif

                            @if($games->status == 2)
                                <div>
                                    <form method="post" action="{{ route('endGame') }}">
                                        @csrf
                                        <input type="text" name="gameId" value="{{$games->id}}" hidden>
                                        <input type="submit" value="Finalizar Partido" class="btn btn-outline-danger">
                                    </form>
                                </div>
                            @endif
                        </div>

                        <form method="post" action="{{ route('setResultGame') }}">
                            @csrf
                            <input type="text" name="gameId" value="{{$games->id}}" hidden>

                            <div class="d-flex justify-content-around mt-5">
                                <div class="text-center">
                                    <img class="avatar" src="{{$games->img1}}">
                                    <br> <br>
                                    <h3> {{$games->team1}} </h3>
                                </div>
                                <div class="text-center">
                                    @if(is_numeric($games->score1))
                                        <input type="number" name="score1" value="{{$games->score1}}" class="w-50">
                                    @else
                                        <input type="number" name="score1" class="w-50">
                                    @endif
                                </div>
                                <div> - </div>
                                <div class="text-center">
                                    @if(is_numeric($games->score2))
                                        <input type="number" name="score2" value="{{$games->score2}}" class="w-50">
                                    @else
                                        <input type="number" name="score2" class="w-50">
                                    @endif
                                </div>
                                <div class="text-center">
                                    <img class="avatar" src="{{$games->img2}}">
                                    <br> <br>
                                    <h3> {{$games->team2}} </h3>
                                </div>
                            </div>

                            @if($games->status == 2)
                                <div class="mt-3">
                                    <input type="submit" value="Actualizar marcador" class="btn btn-success w-100">
                                </div>
                            @endif

                        </form>
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

