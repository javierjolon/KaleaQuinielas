import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

function formatearFecha(fechaStr) {
    const [año, mes, dia] = fechaStr.split('-');
    const date = new Date(+año, +mes - 1, +dia);
    const nombreDia = date.toLocaleDateString('es-MX', { weekday: 'long' });
    const nombreDiaCapital = nombreDia.charAt(0).toUpperCase() + nombreDia.slice(1);
    return `${nombreDiaCapital} ${dia}/${mes}/${año}`;
}

function BloquePartidos({ fecha, juegos, estatusColors }) {
    const todosFinished = juegos.every(j => j.estatus === 'FINISHED');
    const [expandido, setExpandido] = useState(!todosFinished);

    return (
        <div className="mt-6">
            <div
                className="bg-[#d2ee7c] text-[#184530] p-2 text-center font-bold flex flex-row justify-between items-center cursor-pointer select-none px-4"
                onClick={() => setExpandido(!expandido)}
            >
                <div>{formatearFecha(fecha)}</div>
                <div className="flex items-center gap-2">
                    {todosFinished && <span className="text-xs font-normal opacity-60">Finalizados</span>}
                    <span className="text-lg">{expandido ? '▲' : '▼'}</span>
                </div>
            </div>

            {expandido && juegos.map((juego) => (
                <div key={juego.id}>
                    <div className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3 py-1'>

                        <div className='flex flex-col items-center w-2/5'>
                            <div>
                                <img
                                    src={juego.imagenEquipo1 == null ? 'img/static/balon.jpeg' : juego.imagenEquipo1}
                                    alt="imagen"
                                    className='w-10 h-10'
                                />
                            </div>
                            <div>{juego.equipo1 ?? 'Pendiente'}</div>
                        </div>

                        <div className='flex flex-col items-center'>
                            <div>{juego.horaJuego}</div>
                            <div className='flex flex-row items-center'>
                                <div>{juego.resultadoEquipo1 ?? 'Pendiente'}</div>
                                <div className='mx-1'>:</div>
                                <div>{juego.resultadoEquipo2 ?? 'Pendiente'}</div>
                            </div>
                        </div>

                        <div className='flex flex-col items-center w-2/5'>
                            <div>
                                <img
                                    src={juego.imagenEquipo2 == null ? 'img/static/balon.jpeg' : juego.imagenEquipo2}
                                    alt="imagen"
                                    className='w-10 h-10'
                                />
                            </div>
                            <div>{juego.equipo2 ?? 'Pendiente'}</div>
                        </div>

                    </div>
                    <div className='mt-[-1rem] flex flex-row justify-center'>
                        <div className={`w-fit rounded-xl py-1 px-3 text-sm ${estatusColors[juego.estatus] || 'bg-white'}`}>{juego.estatus}</div>
                    </div>
                </div>
            ))}
        </div>
    );
}

export default function Juegos(props) {

    const tabs = [
        { id: "pendientes", label: "Pendientes" },
        { id: "finalizados", label: "Finalizados" },
    ];

    const [activeTab, setActiveTab] = useState(tabs[0].id);

    const estatusColors = {
        'Programado': "bg-[#BFC9D1] text-black",
        'En juego': "bg-dos text-white",
        'Medio tiempo': "bg-white text-black",
        'Finalizdo': "bg-tres text-white",
        'Suspendido': "bg-white text-black",
        'Pospuesto': "bg-white text-black",
        'Cancelado': "bg-white text-black",
        'Gano por default': "bg-white text-black",
    };

    const juegosPendientes = props.juegos.filter(j => j.estatus !== 'FINISHED');
    const juegosFinalizados = props.juegos.filter(j => j.estatus === 'FINISHED');

    const agruparPorFecha = (juegos) => juegos.reduce((acc, juego) => {
        if (!acc[juego.fechaJuego]) acc[juego.fechaJuego] = [];
        acc[juego.fechaJuego].push(juego);
        return acc;
    }, {});

    const pendientesPorFecha = agruparPorFecha(juegosPendientes);
    const finalizadosPorFecha = agruparPorFecha(juegosFinalizados);

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Juegos programados</h2>}
        >
            <Head title="Mi quiniela" />

            <div className="pb-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex border-b border-gray-200 mt-5">
                        {tabs.map(tab => (
                            <button
                                key={tab.id}
                                onClick={() => setActiveTab(tab.id)}
                                className={`px-5 py-2 text-sm font-medium ${
                                    activeTab === tab.id
                                        ? 'border-b-2 border-blue-600 text-blue-600'
                                        : 'text-gray-500 hover:text-gray-700'
                                }`}
                            >
                                {tab.label}
                            </button>
                        ))}
                    </div>

                    {activeTab === "pendientes" && (
                        Object.keys(pendientesPorFecha).length === 0
                            ? <p className="text-center text-gray-500 mt-10">No hay juegos pendientes.</p>
                            : Object.entries(pendientesPorFecha).map(([fecha, juegos]) => (
                                <BloquePartidos key={fecha} fecha={fecha} juegos={juegos} estatusColors={estatusColors} />
                            ))
                    )}

                    {activeTab === "finalizados" && (
                        Object.keys(finalizadosPorFecha).length === 0
                            ? <p className="text-center text-gray-500 mt-10">No hay juegos finalizados.</p>
                            : Object.entries(finalizadosPorFecha).sort(([a], [b]) => b.localeCompare(a)).map(([fecha, juegos]) => (
                                <BloquePartidos key={fecha} fecha={fecha} juegos={juegos} estatusColors={estatusColors} />
                            ))
                    )}
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
