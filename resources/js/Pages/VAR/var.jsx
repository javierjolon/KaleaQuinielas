import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useRef, useEffect, useCallback } from 'react';

function PrediccionBadge({ valor, referencia, mostrar }) {
    if (!mostrar) {
        return <span className="text-gray-400 text-sm italic">Sin quiniela</span>;
    }
    const correcto = referencia !== null && referencia !== undefined && parseInt(valor) === parseInt(referencia);
    return (
        <span className={`font-bold text-lg ${correcto ? 'text-green-600' : 'text-gray-800'}`}>
            {valor ?? '-'}
        </span>
    );
}

function ganador(equipo1, equipo2) {
    const e1 = parseInt(equipo1);
    const e2 = parseInt(equipo2);
    if (isNaN(e1) || isNaN(e2)) return null;
    if (e1 > e2) return 'L';
    if (e1 < e2) return 'V';
    return 'E';
}

function GanadorBadge({ quinielaEquipo1, quinielaEquipo2, resultadoEquipo1, resultadoEquipo2, mostrar, equipo1Nombre, equipo2Nombre }) {
    if (!mostrar) return <span className="text-gray-400 text-sm italic">-</span>;

    const predicho = ganador(quinielaEquipo1, quinielaEquipo2);
    const real     = (resultadoEquipo1 !== null && resultadoEquipo2 !== null)
        ? ganador(resultadoEquipo1, resultadoEquipo2)
        : null;

    const etiquetas = { L: equipo1Nombre, V: equipo2Nombre, E: 'Empate' };
    const texto = etiquetas[predicho] ?? '-';
    const correcto = real !== null && predicho === real;

    return (
        <span className={`inline-block text-xs font-semibold px-2 py-0.5 rounded-md text-center leading-tight ${correcto ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'}`}>
            {texto}
        </span>
    );
}

function CabeceraContenido({ juego }) {
    return (
        <>
            <div className="flex items-center justify-between mb-1">
                <span className="text-xs text-gray-500">{juego.tipoJuego}</span>
                <span
                    className="text-xs font-semibold px-2 py-0.5 rounded-full"
                    style={{ backgroundColor: juego.estatusColor + '20', color: juego.estatusColor }}
                >
                    {juego.estatusNombre}
                </span>
            </div>
            <div className="flex items-center justify-between gap-4 mt-3">
                <div className="flex flex-col items-center flex-1">
                    {juego.imagenEquipo1 && (
                        <div className="bg-gray-100 rounded-2xl p-2 flex items-center justify-center mb-1">
                            <img src={juego.imagenEquipo1} alt={juego.equipo1} className="h-12 w-12 object-contain" />
                        </div>
                    )}
                    <span className="text-sm font-medium text-center">{juego.equipo1}</span>
                </div>
                <div className="flex flex-col items-center">
                    <div className="flex items-center gap-2">
                        <span className="text-3xl font-bold text-gray-900">{juego.resultadoEquipo1 ?? '-'}</span>
                        <span className="text-2xl text-gray-400">:</span>
                        <span className="text-3xl font-bold text-gray-900">{juego.resultadoEquipo2 ?? '-'}</span>
                    </div>
                </div>
                <div className="flex flex-col items-center flex-1">
                    {juego.imagenEquipo2 && (
                        <div className="bg-gray-100 rounded-2xl p-2 flex items-center justify-center mb-1">
                            <img src={juego.imagenEquipo2} alt={juego.equipo2} className="h-12 w-12 object-contain" />
                        </div>
                    )}
                    <span className="text-sm font-medium text-center">{juego.equipo2}</span>
                </div>
            </div>
        </>
    );
}

function TarjetaJuego({ juego, cardRef }) {
    return (
        <div ref={cardRef} className="bg-white rounded-xl shadow-sm mb-6">
            <div className="px-6 py-4 border-b border-gray-100 rounded-t-xl">
                <CabeceraContenido juego={juego} />
            </div>

            <div className="overflow-x-auto">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-left text-xs text-gray-500 uppercase">
                            <th className="px-4 py-2">Participante</th>
                            <th className="px-4 py-2 text-center">{juego.equipo1}</th>
                            <th className="px-4 py-2 text-center">{juego.equipo2}</th>
                            <th className="px-4 py-2 text-center">Ganador</th>
                            <th className="px-4 py-2 text-center">Pts</th>
                        </tr>
                    </thead>
                    <tbody>
                        {juego.predicciones.length === 0 ? (
                            <tr>
                                <td colSpan="5" className="px-4 py-4 text-center text-gray-400">
                                    No hay participantes.
                                </td>
                            </tr>
                        ) : (
                            juego.predicciones.map((p) => {
                                const tieneQuiniela = p.quinielaEquipo1 !== null && p.quinielaEquipo2 !== null;
                                return (
                                    <tr key={p.usuarioId} className="border-t border-gray-50 hover:bg-gray-50">
                                        <td className="px-4 py-3 font-medium text-gray-800">{p.name}</td>
                                        <td className="px-4 py-3 text-center">
                                            <PrediccionBadge valor={p.quinielaEquipo1} referencia={juego.resultadoEquipo1} mostrar={tieneQuiniela} />
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <PrediccionBadge valor={p.quinielaEquipo2} referencia={juego.resultadoEquipo2} mostrar={tieneQuiniela} />
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <GanadorBadge
                                                quinielaEquipo1={p.quinielaEquipo1}
                                                quinielaEquipo2={p.quinielaEquipo2}
                                                resultadoEquipo1={juego.resultadoEquipo1}
                                                resultadoEquipo2={juego.resultadoEquipo2}
                                                mostrar={tieneQuiniela}
                                                equipo1Nombre={juego.equipo1}
                                                equipo2Nombre={juego.equipo2}
                                            />
                                        </td>
                                        <td className="px-4 py-3 text-center">
                                            <span className={`font-semibold ${p.puntosXjuego > 0 ? 'text-green-600' : 'text-gray-400'}`}>
                                                {tieneQuiniela ? (p.puntosXjuego ?? 0) : '-'}
                                            </span>
                                        </td>
                                    </tr>
                                );
                            })
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function Var(props) {
    const { quinielaActiva } = usePage().props;
    const juegosEnCurso     = props.juegosEnCurso ?? [];
    const juegosFinalizados = props.juegosFinalizados ?? [];
    const nombreQuiniela    = quinielaActiva?.nombre ?? 'Sin quiniela activa';
    const [tab, setTab]     = useState('enCurso');
    const [activeJuego, setActiveJuego] = useState(null);
    const cardRefs = useRef({});

    const currentList = tab === 'enCurso' ? juegosEnCurso : juegosFinalizados;

    const handleScroll = useCallback(() => {
        let current = null;
        for (const juego of currentList) {
            const el = cardRefs.current[juego.id];
            if (!el) continue;
            const rect = el.getBoundingClientRect();
            if (rect.top <= 0) current = juego;
        }
        setActiveJuego(current);
    }, [currentList]);

    useEffect(() => {
        window.addEventListener('scroll', handleScroll, { passive: true });
        return () => window.removeEventListener('scroll', handleScroll);
    }, [handleScroll]);

    useEffect(() => { setActiveJuego(null); }, [tab]);

    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['juegosEnCurso', 'juegosFinalizados'] });
        }, 30000);
        return () => clearInterval(interval);
    }, []);

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">VAR</h2>}
        >
            <Head title="VAR" />

            {activeJuego && (
                <div className="fixed top-0 left-3 right-3 z-20 bg-white border border-gray-200 shadow-md rounded-xl px-6 py-4 max-w-3xl mx-auto">
                    <CabeceraContenido juego={activeJuego} />
                </div>
            )}

            <div className="py-8 m-3 sm:m-0">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <p className="text-sm text-gray-500 mb-4 text-center">
                        Quiniela: <span className="font-medium text-gray-700">{nombreQuiniela}</span>
                    </p>

                    <div className="flex border-b border-gray-200 mb-6">
                        <button
                            onClick={() => setTab('enCurso')}
                            className={`px-5 py-2 text-sm font-medium ${tab === 'enCurso' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            En Curso
                        </button>
                        <button
                            onClick={() => setTab('historial')}
                            className={`px-5 py-2 text-sm font-medium ${tab === 'historial' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500 hover:text-gray-700'}`}
                        >
                            Historial
                            {juegosFinalizados.length > 0 && (
                                <span className="ml-2 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">
                                    {juegosFinalizados.length}
                                </span>
                            )}
                        </button>
                    </div>

                    {tab === 'enCurso' && (
                        juegosEnCurso.length === 0 ? (
                            <div className="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                                <span className="material-symbols-outlined text-5xl block mb-3">sports_soccer</span>
                                No hay juegos en curso en este momento.
                            </div>
                        ) : (
                            juegosEnCurso.map((juego) => (
                                <TarjetaJuego
                                    key={juego.id}
                                    juego={juego}
                                    cardRef={el => cardRefs.current[juego.id] = el}
                                />
                            ))
                        )
                    )}

                    {tab === 'historial' && (
                        juegosFinalizados.length === 0 ? (
                            <div className="bg-white rounded-xl shadow-sm p-10 text-center text-gray-400">
                                <span className="material-symbols-outlined text-5xl block mb-3">history</span>
                                No hay juegos finalizados aún.
                            </div>
                        ) : (
                            juegosFinalizados.map((juego) => (
                                <TarjetaJuego
                                    key={juego.id}
                                    juego={juego}
                                    cardRef={el => cardRefs.current[juego.id] = el}
                                />
                            ))
                        )
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
