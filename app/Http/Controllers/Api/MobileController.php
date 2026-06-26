<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class MobileController extends Controller
{
    // ─── AUTH ────────────────────────────────────────────────────────────────

    public function login(Request $request)
    {
        $request->validate([
            'telefono' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('telefono', $request->telefono)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'telefono' => ['Credenciales incorrectas.'],
            ]);
        }

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'telefono' => 'required|string|regex:/^[0-9+\s\-]{8,20}$/|unique:users',
            'pais'     => 'nullable|string|max:10',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'telefono' => $data['telefono'],
            'pais'     => $data['pais'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $user->markEmailAsVerified();

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $this->userPayload($user),
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Sesión cerrada.']);
    }

    public function user(Request $request)
    {
        return response()->json($this->userPayload($request->user()));
    }

    // ─── DASHBOARD ───────────────────────────────────────────────────────────

    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;
        $quinielaId = (int) $request->input('quinielaId', 0);

        $quinielas = DB::table('usuariosQuinielas as uq')
            ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
            ->select('q.id', 'q.nombre', 'q.status', 'q.usuarioId')
            ->where('uq.usuarioId', $userId)
            ->orderBy('q.nombre')
            ->get();

        if ($quinielas->isEmpty()) {
            return response()->json([
                'quinielas'       => [],
                'quinielaActiva'  => null,
                'usuariosQuiniela'=> [],
            ]);
        }

        $quinielaActiva = $quinielas->firstWhere('id', $quinielaId)
            ?? $quinielas->first();

        $quinielaActivaId = (int) $quinielaActiva->id;

        $usuariosQuiniela = DB::table('usuariosQuinielas as uq')
            ->join('users as u', 'u.id', '=', 'uq.usuarioId')
            ->select(
                'u.id', 'u.name', 'u.telefono', 'uq.subeBaja',
                DB::raw("(SELECT COALESCE(SUM(qj.puntosXjuego),0) FROM quinielasJuegos qj INNER JOIN juegos j ON j.id = qj.juegoId WHERE qj.quinielaId = {$quinielaActivaId} AND qj.usuarioId = u.id AND qj.status = 'FINISHED' AND j.estatus = 'FINISHED') as puntosAcumulados")
            )
            ->where('uq.quinielaId', $quinielaActivaId)
            ->orderByDesc('puntosAcumulados')
            ->orderBy('u.name')
            ->get();

        return response()->json([
            'quinielas'        => $quinielas->map(fn($q) => [
                'id'        => $q->id,
                'nombre'    => $q->nombre,
                'status'    => $q->status,
                'usuarioId' => $q->usuarioId,
                'activo'    => $q->id === $quinielaActiva->id,
            ])->values(),
            'quinielaActiva'   => [
                'id'        => $quinielaActiva->id,
                'nombre'    => $quinielaActiva->nombre,
                'status'    => $quinielaActiva->status,
                'usuarioId' => $quinielaActiva->usuarioId,
            ],
            'usuariosQuiniela' => $usuariosQuiniela,
        ]);
    }

    // ─── QUINIELA GAMES ──────────────────────────────────────────────────────

    public function quiniela(Request $request)
    {
        $userId = $request->user()->id;
        $quinielaId = (int) $request->input('quinielaId', 0);

        if ($quinielaId <= 0) {
            $quinielaId = (int) (DB::table('usuariosQuinielas as uq')
                ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
                ->where('uq.usuarioId', $userId)
                ->where('q.status', 'TIMED')
                ->value('q.id') ?? 0);
        }

        if ($quinielaId <= 0) {
            return response()->json([
                'juegosPendientes'  => [],
                'juegosIngresados'  => [],
                'juegosFinalizados' => [],
            ]);
        }

        $base = DB::table('quinielasJuegos as qj')
            ->select(
                'qj.quinielaEquipo1', 'qj.quinielaEquipo2',
                'qj.status as estatusQuiniela', 'qj.juegoId as id', 'qj.puntosXjuego',
                'juegos.equipo1', 'juegos.equipo2',
                'juegos.imagenEquipo1', 'juegos.imagenEquipo2',
                'juegos.ronda', 'juegos.fechaJuego', 'juegos.horaJuego',
                'juegos.estatus as estatusJuego',
                'juegos.resultadoEquipo1', 'juegos.resultadoEquipo2'
            )
            ->leftJoin('juegos', 'qj.juegoId', 'juegos.id')
            ->where('qj.usuarioId', $userId)
            ->where('qj.quinielaId', $quinielaId);

        $pendientes = (clone $base)
            ->where(function ($q) {
                $q->whereDate('juegos.fechaJuego', '>=', Carbon::today()->toDateString())
                  ->orWhereIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED']);
            })
            ->whereNull('qj.quinielaEquipo1')
            ->whereNull('qj.quinielaEquipo2')
            ->whereNotIn('juegos.estatus', ['FINISHED', 'AWARDED', 'CANCELLED', 'POSTPONED', 'SUSPENDED'])
            ->orderBy('juegos.fechaJuego')->orderBy('juegos.horaJuego')
            ->get()->map(fn($g) => $this->mapGame($g));

        $ingresados = (clone $base)
            ->where(function ($q) {
                $q->whereDate('juegos.fechaJuego', '>=', Carbon::today()->toDateString())
                  ->orWhereIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED']);
            })
            ->whereNotNull('qj.quinielaEquipo1')
            ->whereNotNull('qj.quinielaEquipo2')
            ->where(function ($q) {
                $q->whereNotIn('qj.status', ['FINISHED', 'INVALID'])
                  ->orWhereIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED']);
            })
            ->whereNotIn('juegos.estatus', ['FINISHED', 'AWARDED', 'CANCELLED', 'POSTPONED', 'SUSPENDED'])
            ->orderBy('juegos.fechaJuego')->orderBy('juegos.horaJuego')
            ->get()->map(fn($g) => $this->mapGame($g));

        $finalizados = (clone $base)
            ->whereIn('qj.status', ['FINISHED', 'INVALID'])
            ->whereNotIn('juegos.estatus', ['IN_PLAY', 'LIVE', 'PAUSED'])
            ->orderByDesc('juegos.fechaJuego')->orderBy('juegos.horaJuego')
            ->get()->map(fn($g) => $this->mapGame($g));

        return response()->json([
            'juegosPendientes'  => $this->groupByDate($pendientes),
            'juegosIngresados'  => $this->groupByDate($ingresados),
            'juegosFinalizados' => $this->groupByDate($finalizados),
        ]);
    }

    public function patchPrediccion(Request $request, $juegoId)
    {
        $data = $request->validate([
            'quinielaEquipo1' => 'required|integer|min:0',
            'quinielaEquipo2' => 'required|integer|min:0',
        ]);

        $userId = $request->user()->id;

        $rows = DB::table('quinielasJuegos')
            ->where('juegoId', $juegoId)
            ->where('usuarioId', $userId)
            ->get();

        if ($rows->isEmpty()) {
            return response()->json(['message' => 'Juego no encontrado.'], 404);
        }

        DB::table('quinielasJuegos')
            ->where('juegoId', $juegoId)
            ->where('usuarioId', $userId)
            ->update([
                'quinielaEquipo1' => $data['quinielaEquipo1'],
                'quinielaEquipo2' => $data['quinielaEquipo2'],
            ]);

        return response()->json(['message' => 'Actualizado correctamente.']);
    }

    // ─── QUINIELA MANAGEMENT ─────────────────────────────────────────────────

    public function crearQuiniela(Request $request)
    {
        $data = $request->validate([
            'nombre'      => 'required|string|max:120',
            'competicion' => 'required|string',
            'season'      => 'required|integer',
        ]);

        $userId = $request->user()->id;

        $existe = DB::table('quinielas')
            ->where('usuarioId', $userId)
            ->where('nombre', $data['nombre'])
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya existe una quiniela con ese nombre.'], 422);
        }

        $quinielaId = DB::table('quinielas')->insertGetId([
            'usuarioId' => $userId,
            'nombre'    => $data['nombre'],
            'status'    => 'TIMED',
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);

        DB::table('usuariosQuinielas')->insert([
            'usuarioId'  => $userId,
            'quinielaId' => $quinielaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $juegos = DB::table('juegos')
            ->where('competicion', $data['competicion'])
            ->where('season', $data['season'])
            ->whereNotIn('estatus', ['FINISHED', 'AWARDED', 'CANCELLED'])
            ->select('id')
            ->get();

        foreach ($juegos as $juego) {
            DB::table('quinielasJuegos')->insert([
                'usuarioId'   => $userId,
                'quinielaId'  => $quinielaId,
                'juegoId'     => $juego->id,
                'status'      => 'TIMED',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        return response()->json([
            'message'  => 'Quiniela creada correctamente.',
            'quiniela' => [
                'id'        => $quinielaId,
                'nombre'    => $data['nombre'],
                'status'    => 'TIMED',
                'usuarioId' => $userId,
            ],
        ], 201);
    }

    public function agregarUsuario(Request $request)
    {
        $data = $request->validate([
            'telefono'   => 'required|string',
            'quinielaId' => 'required|integer',
        ]);

        $quinielaId = (int) $data['quinielaId'];
        $userId = $request->user()->id;

        $esDuenio = DB::table('quinielas')
            ->where('id', $quinielaId)
            ->where('usuarioId', $userId)
            ->exists();

        if (! $esDuenio) {
            return response()->json(['message' => 'No tienes permiso para agregar usuarios.'], 403);
        }

        $nuevoUsuario = User::where('telefono', $data['telefono'])->first();
        if (! $nuevoUsuario) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $yaExiste = DB::table('usuariosQuinielas')
            ->where('quinielaId', $quinielaId)
            ->where('usuarioId', $nuevoUsuario->id)
            ->exists();

        if ($yaExiste) {
            return response()->json(['message' => 'El usuario ya está en la quiniela.'], 422);
        }

        DB::table('usuariosQuinielas')->insert([
            'usuarioId'  => $nuevoUsuario->id,
            'quinielaId' => $quinielaId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $juegosExistentes = DB::table('quinielasJuegos')
            ->where('quinielaId', $quinielaId)
            ->where('usuarioId', $userId)
            ->select('juegoId')
            ->get();

        foreach ($juegosExistentes as $juego) {
            $existe = DB::table('quinielasJuegos')
                ->where('quinielaId', $quinielaId)
                ->where('usuarioId', $nuevoUsuario->id)
                ->where('juegoId', $juego->juegoId)
                ->exists();

            if (! $existe) {
                DB::table('quinielasJuegos')->insert([
                    'usuarioId'  => $nuevoUsuario->id,
                    'quinielaId' => $quinielaId,
                    'juegoId'    => $juego->juegoId,
                    'status'     => 'TIMED',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        return response()->json(['message' => 'Usuario agregado correctamente.']);
    }

    public function eliminarUsuario(Request $request)
    {
        $data = $request->validate([
            'usuarioId'  => 'required|integer',
            'quinielaId' => 'required|integer',
        ]);

        $ownerId = $request->user()->id;
        $quinielaId = (int) $data['quinielaId'];

        $esDuenio = DB::table('quinielas')
            ->where('id', $quinielaId)
            ->where('usuarioId', $ownerId)
            ->exists();

        if (! $esDuenio) {
            return response()->json(['message' => 'No tienes permiso.'], 403);
        }

        DB::table('usuariosQuinielas')
            ->where('quinielaId', $quinielaId)
            ->where('usuarioId', $data['usuarioId'])
            ->delete();

        return response()->json(['message' => 'Usuario eliminado.']);
    }

    // ─── TEAM MATCHES MODAL ──────────────────────────────────────────────────

    public function partidosEquipo(Request $request)
    {
        $imagen = $request->input('imagen');
        if (! $imagen) {
            return response()->json([]);
        }

        $competicion = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->where('qj.usuarioId', $request->user()->id)
            ->whereNotNull('j.competicion')
            ->select('j.competicion', 'j.season')
            ->first();

        if (! $competicion) {
            return response()->json([]);
        }

        $partidos = DB::table('juegos')
            ->where('competicion', $competicion->competicion)
            ->where('season', $competicion->season)
            ->where(function ($q) use ($imagen) {
                $q->where('imagenEquipo1', $imagen)->orWhere('imagenEquipo2', $imagen);
            })
            ->orderBy('fechaJuego')->orderBy('horaJuego')
            ->get();

        return response()->json($partidos->map(function ($p) use ($imagen) {
            $esLocal = $p->imagenEquipo1 === $imagen;
            return [
                'id'          => $p->id,
                'fecha'       => Carbon::parse($p->fechaJuego)->format('d/m/Y'),
                'hora'        => substr($p->horaJuego ?? '', 0, 5),
                'rival'       => traducir_equipos($esLocal ? ($p->equipo2 ?? 'Pendiente') : ($p->equipo1 ?? 'Pendiente')),
                'imagenRival' => $esLocal ? $p->imagenEquipo2 : $p->imagenEquipo1,
                'esLocal'     => $esLocal,
                'goles'       => $p->resultadoEquipo1 !== null
                    ? ($esLocal
                        ? $p->resultadoEquipo1 . '-' . $p->resultadoEquipo2
                        : $p->resultadoEquipo2 . '-' . $p->resultadoEquipo1)
                    : null,
                'estatus'     => $p->estatus,
            ];
        }));
    }

    // ─── LIGA TABLE ──────────────────────────────────────────────────────────

    public function tablaLiga(Request $request)
    {
        $quinielaId = (int) $request->input('quinielaId', 0);

        if ($quinielaId <= 0) {
            return response()->json([]);
        }

        $competicion = DB::table('quinielasJuegos as qj')
            ->join('juegos as j', 'j.id', '=', 'qj.juegoId')
            ->where('qj.quinielaId', $quinielaId)
            ->whereNotNull('j.competicion')
            ->select('j.competicion', 'j.season')
            ->first();

        if (! $competicion) {
            return response()->json([]);
        }

        $response = Http::withHeaders([
            'x-apisports-key' => env('FOOTBALL_API_KEY'),
        ])->get("https://v3.football.api-sports.io/standings?league={$competicion->competicion}&season={$competicion->season}");

        if (! $response->successful()) {
            return response()->json([], 502);
        }

        $data      = $response->json();
        $leagueRaw = $data['response'][0]['league'] ?? null;

        if (! $leagueRaw) {
            return response()->json([]);
        }

        $grupos = collect($leagueRaw['standings'] ?? [])->map(function ($grupo) {
            return collect($grupo)->map(function ($item) {
                return [
                    'rank'  => $item['rank'],
                    'team'  => [
                        'name' => traducir_equipos($item['team']['name']),
                        'logo' => $item['team']['logo'],
                    ],
                    'points' => $item['points'],
                    'all'    => $item['all'],
                ];
            })->values();
        })->values();

        return response()->json([
            'league' => ['nombre' => $leagueRaw['name'], 'logo' => $leagueRaw['logo']],
            'grupos' => $grupos,
        ]);
    }

    // ─── HELPERS ─────────────────────────────────────────────────────────────

    private function userPayload(User $user): array
    {
        return [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->telefono,
            'telefono' => $user->telefono,
        ];
    }

    private function mapGame(object $game): object
    {
        $game->equipo1         = traducir_equipos($game->equipo1 ?? 'Pendiente');
        $game->equipo2         = traducir_equipos($game->equipo2 ?? 'Pendiente');
        $game->estatusQuiniela = ['nombre' => traducir_estatus($game->estatusQuiniela ?? ' '), 'color' => color_estatus($game->estatusQuiniela ?? ' ')];
        $game->estatusJuego    = ['nombre' => traducir_estatus($game->estatusJuego ?? ' '), 'color' => color_estatus($game->estatusJuego ?? ' ')];
        $game->tipoJuego       = traducir_rondas($game->ronda ?? ' ');
        return $game;
    }

    private function groupByDate($collection): array
    {
        return $collection->groupBy(function ($juego) {
            return Carbon::parse($juego->fechaJuego)->format('d-m-Y');
        })->toArray();
    }
}
