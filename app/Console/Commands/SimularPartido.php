<?php

namespace App\Console\Commands;

use App\Http\Controllers\GamesController;
use App\Models\Juegos;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimularPartido extends Command
{
    protected $signature = 'simular:partido
                            {--status=1H : Status short (NS, 1H, HT, 2H, FT)}
                            {--home=0    : Goles equipo local}
                            {--away=0    : Goles equipo visitante}';

    protected $description = 'Simula un paso del partido Haiti vs Scotland (fixture 1489372 / juego 576) usando JSON de api-sports.io';

    // JSON base copiado de api-sports.io v3 para este fixture
    private function buildFixture(string $statusShort, ?int $home, ?int $away): array
    {
        $elapsed = match($statusShort) {
            'NS'  => null,
            '1H'  => 45,
            'HT'  => 45,
            '2H'  => 90,
            'ET'  => 105,
            'FT'  => 90,
            default => null,
        };

        $goalsHome = in_array($statusShort, ['NS']) ? null : $home;
        $goalsAway = in_array($statusShort, ['NS']) ? null : $away;

        return [
            "fixture" => [
                "id"        => 1489372,
                "referee"   => null,
                "timezone"  => "UTC",
                "date"      => "2026-06-13T19:00:00+00:00",
                "timestamp" => 1750543200,
                "periods"   => [
                    "first"  => $statusShort === 'NS' ? null : 1750543200,
                    "second" => in_array($statusShort, ['2H', 'FT']) ? 1750550400 : null,
                ],
                "venue"  => ["id" => null, "name" => null, "city" => null],
                "status" => [
                    "long"    => $this->statusLong($statusShort),
                    "short"   => $statusShort,
                    "elapsed" => $elapsed,
                ],
            ],
            "league" => [
                "id"      => 1,
                "name"    => "World Cup",
                "country" => "World",
                "logo"    => "https://media.api-sports.io/football/leagues/1.png",
                "flag"    => null,
                "season"  => 2026,
                "round"   => "Group Stage - 1",
            ],
            "teams" => [
                "home" => [
                    "id"     => 2386,
                    "name"   => "Haiti",
                    "logo"   => "https://media.api-sports.io/football/teams/2386.png",
                    "winner" => $statusShort === 'FT' ? ($home > $away ? true : ($home < $away ? false : null)) : null,
                ],
                "away" => [
                    "id"     => 1108,
                    "name"   => "Scotland",
                    "logo"   => "https://media.api-sports.io/football/teams/1108.png",
                    "winner" => $statusShort === 'FT' ? ($away > $home ? true : ($away < $home ? false : null)) : null,
                ],
            ],
            "goals" => [
                "home" => $goalsHome,
                "away" => $goalsAway,
            ],
            "score" => [
                "halftime"  => ["home" => null, "away" => null],
                "fulltime"  => ["home" => $statusShort === 'FT' ? $home : null, "away" => $statusShort === 'FT' ? $away : null],
                "extratime" => ["home" => null, "away" => null],
                "penalty"   => ["home" => null, "away" => null],
            ],
        ];
    }

    private function statusLong(string $short): string
    {
        return match($short) {
            'NS'  => 'Not Started',
            '1H'  => 'First Half',
            'HT'  => 'Halftime',
            '2H'  => 'Second Half',
            'ET'  => 'Extra Time',
            'FT'  => 'Match Finished',
            default => $short,
        };
    }

    private function mapearEstatus(string $short): string
    {
        return match($short) {
            'NS', 'TBD'            => 'TIMED',
            '1H', '2H', 'ET', 'P',
            'INT', 'LIVE'          => 'IN_PLAY',
            'HT', 'BT'             => 'PAUSED',
            'FT', 'AET', 'PEN'     => 'FINISHED',
            default                => $short,
        };
    }

    public function handle(GamesController $gamesController): int
    {
        $statusShort = strtoupper($this->option('status'));
        $home        = (int) $this->option('home');
        $away        = (int) $this->option('away');

        $fixture = $this->buildFixture($statusShort, $home, $away);

        $this->line('');
        $this->info('=== JSON fixture enviado ===');
        $this->line(json_encode($fixture, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line('');

        // Procesar igual que el servicio real
        $statusApi      = $this->mapearEstatus($statusShort);
        $scoreHome      = $fixture['goals']['home'];
        $scoreAway      = $fixture['goals']['away'];
        $fechaJuego     = Carbon::parse($fixture['fixture']['date']);
        $juegoYaEmpezó  = $fechaJuego->isPast();

        $juego = Juegos::find(576);
        if (!$juego) {
            $this->error('Juego 576 no encontrado en la base de datos.');
            return Command::FAILURE;
        }

        $estatusAntes = $juego->estatus;

        // Misma lógica de protección que apiFotballService
        $statusNoRegresa = ['IN_PLAY', 'PAUSED', 'FINISHED'];
        if (in_array($estatusAntes, $statusNoRegresa) && $statusApi === 'TIMED') {
            $statusEfectivo = $estatusAntes;
        } elseif ($statusApi === 'TIMED' && $juegoYaEmpezó) {
            $statusEfectivo = 'IN_PLAY';
        } else {
            $statusEfectivo = $statusApi;
        }

        $campos = [
            'estatus'    => $statusEfectivo,
            'updated_at' => now(),
        ];

        if ($statusApi === 'FINISHED' && $scoreHome !== null && $scoreAway !== null) {
            $campos['resultadoEquipo1'] = $scoreHome;
            $campos['resultadoEquipo2'] = $scoreAway;
        } elseif ($statusApi !== 'TIMED' && $scoreHome !== null && $scoreAway !== null) {
            $campos['resultadoEquipo1'] = $scoreHome;
            $campos['resultadoEquipo2'] = $scoreAway;
        }

        $juego->update($campos);

        $enJuego         = in_array($statusEfectivo, ['IN_PLAY', 'PAUSED']);
        $cambioDeEstatus = $estatusAntes !== $statusEfectivo;
        $acabaDeTerminar = $cambioDeEstatus && $statusApi === 'FINISHED';
        $scoreChanged    = $statusApi === 'FINISHED'
                        && $scoreHome !== null && $scoreAway !== null
                        && ((int)($juego->resultadoEquipo1) !== $scoreHome
                            || (int)($juego->resultadoEquipo2) !== $scoreAway);

        $this->info("Estatus: {$estatusAntes} → {$statusEfectivo}");
        $this->info("Marcador: " . ($scoreHome ?? '-') . " - " . ($scoreAway ?? '-'));

        if ($enJuego || $acabaDeTerminar || $scoreChanged) {
            $gamesController->ApiActualizarPuntaje(576, $acabaDeTerminar || $scoreChanged);
            $this->info('Puntajes recalculados.');
        } else {
            $this->warn('Sin cambios que disparen recálculo.');
        }

        // Mostrar tabla de posiciones actual de quiniela 10
        $this->line('');
        $this->info('=== Posiciones quiniela 10 ===');
        $posiciones = DB::table('usuariosQuinielas as uq')
            ->join('users as u', 'u.id', '=', 'uq.usuarioId')
            ->leftJoin(DB::raw("(SELECT qj.usuarioId, SUM(qj.puntosXjuego) as total
                                  FROM quinielasJuegos qj
                                  INNER JOIN juegos j ON j.id = qj.juegoId
                                  WHERE qj.quinielaId = 10
                                  GROUP BY qj.usuarioId) as pts"), 'pts.usuarioId', '=', 'uq.usuarioId')
            ->select('u.name', 'uq.posicion', 'uq.subeBaja', DB::raw('COALESCE(pts.total,0) as puntos'))
            ->where('uq.quinielaId', 10)
            ->orderBy('uq.posicion')
            ->get();

        $this->table(['Pos', 'Usuario', 'Puntos', '↕'], $posiciones->map(fn($r) => [
            $r->posicion, $r->name, $r->puntos, $r->subeBaja ?? '-'
        ])->toArray());

        // Apuestas del juego 576
        $this->line('');
        $this->info('=== Apuestas juego 576 ===');
        $apuestas = DB::table('quinielasJuegos as qj')
            ->join('users as u', 'u.id', '=', 'qj.usuarioId')
            ->select('u.name', 'qj.quinielaEquipo1', 'qj.quinielaEquipo2', 'qj.puntosXjuego', 'qj.status')
            ->where('qj.juegoId', 576)
            ->orderByDesc('qj.puntosXjuego')
            ->get();

        $this->table(['Usuario', 'Apuesta', 'Puntos', 'Status'], $apuestas->map(fn($r) => [
            $r->name,
            "{$r->quinielaEquipo1} - {$r->quinielaEquipo2}",
            $r->puntosXjuego,
            $r->status,
        ])->toArray());

        return Command::SUCCESS;
    }
}
