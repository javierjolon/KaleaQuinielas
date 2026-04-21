<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class VarController extends Controller
{
    public function index()
    {
        $quinielaActiva = collect(session('quinielas', []))->firstWhere('activo', true);
        $quinielaActivaId = (int) ($quinielaActiva['id'] ?? 0);

        if ($quinielaActivaId <= 0) {
            return Inertia::render('VAR/var', [
                'juegosEnCurso'     => [],
                'juegosFinalizados' => [],
                'quinielaActiva'    => null,
            ]);
        }

        $juegosEnCurso = $this->queryJuegos($quinielaActivaId, ['IN_PLAY', 'LIVE', 'PAUSED']);

        $juegosFinalizados = $this->queryJuegos($quinielaActivaId, ['FINISHED']);

        return Inertia::render('VAR/var', [
            'juegosEnCurso'     => $juegosEnCurso,
            'juegosFinalizados' => $juegosFinalizados,
            'quinielaActiva'    => $quinielaActiva,
        ]);
    }

    private function queryJuegos(int $quinielaId, array $estatus)
    {
        $juegos = DB::table('juegos as j')
            ->join('quinielasJuegos as qj', 'qj.juegoId', '=', 'j.id')
            ->where('qj.quinielaId', '=', $quinielaId)
            ->whereIn('j.estatus', $estatus)
            ->select(
                'j.id', 'j.equipo1', 'j.equipo2',
                'j.imagenEquipo1', 'j.imagenEquipo2',
                'j.resultadoEquipo1', 'j.resultadoEquipo2',
                'j.estatus', 'j.ronda', 'j.fechaJuego', 'j.horaJuego'
            )
            ->distinct()
            ->orderByDesc('j.fechaJuego')
            ->orderByDesc('j.horaJuego')
            ->get();

        return $juegos->map(function ($juego) use ($quinielaId) {
            $predicciones = DB::table('quinielasJuegos as qj')
                ->join('users as u', 'u.id', '=', 'qj.usuarioId')
                ->where('qj.quinielaId', '=', $quinielaId)
                ->where('qj.juegoId', '=', $juego->id)
                ->select(
                    'u.id as usuarioId',
                    'u.name',
                    'qj.quinielaEquipo1',
                    'qj.quinielaEquipo2',
                    'qj.puntosXjuego',
                    'qj.status'
                )
                ->orderByDesc('qj.puntosXjuego')
                ->orderBy('u.name')
                ->get();

            $juego->equipo1       = traducir_equipos($juego->equipo1 ?? 'Pendiente');
            $juego->equipo2       = traducir_equipos($juego->equipo2 ?? 'Pendiente');
            $juego->estatusNombre = traducir_estatus($juego->estatus ?? ' ');
            $juego->estatusColor  = color_estatus($juego->estatus ?? ' ');
            $juego->tipoJuego     = traducir_rondas($juego->ronda ?? ' ');
            $juego->predicciones  = $predicciones;

            return $juego;
        });
    }
}
