<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $quinielasHeader = [];
        $quinielaActivaId = null;

        if ($request->user()) {
            $quinielas = DB::table('usuariosQuinielas as uq')
                ->join('quinielas as q', 'q.id', '=', 'uq.quinielaId')
                ->select('q.id', 'q.nombre')
                ->where('uq.usuarioId', '=', $request->user()->id)
                ->where('q.status', '=', 'TIMED')
                ->orderBy('q.nombre')
                ->get();

            $quinielasSessionActual = collect(session('quinielas', []));
            $quinielaActivaActual = $quinielasSessionActual->firstWhere('activo', true);
            $quinielaActivaId = $quinielaActivaActual['id'] ?? null;

            $quinielasHeader = $quinielas->values()
                ->map(function ($q, $index) use ($quinielaActivaId) {
                    return [
                        'id' => $q->id,
                        'nombre' => $q->nombre,
                        'activo' => $quinielaActivaId !== null
                            ? (int) $q->id === (int) $quinielaActivaId
                            : $index === 0,
                    ];
                })
                ->all();
        }

        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'quinielasHeader' => $quinielasHeader,
            'quinielaActivaId' => $quinielaActivaId,
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                ]);
            },
        ]);
    }
}
