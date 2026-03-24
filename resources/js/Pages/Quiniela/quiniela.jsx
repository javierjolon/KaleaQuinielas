import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { useState } from "react";



export default function Quiniela(props) {

    const tabs = [
        { id: "pendientes", label: "Pendientes" },
        { id: "ingresadas", label: "Ingresadas" },
        { id: "finalizados", label: "Finalizados" }
      ];

    const [activeTab, setActiveTab] = useState(tabs[0].id);
    const [resultados, setResultados] = useState({});

    const juegosPorFecha = props.juegosPendientes.reduce((acc, juegosPendientes) => {
        if (!acc[juegosPendientes.dateGame]) {
            acc[juegosPendientes.dateGame] = [];
        }
        acc[juegosPendientes.dateGame].push(juegosPendientes);
        return acc;
    }, {});

    const juegosPorFecha2 = props.juegosIngresados.reduce((acc2, juegosIngresados) => {
        if (!acc2[juegosIngresados.dateGame]) {
            acc2[juegosIngresados.dateGame] = [];
        }
        acc2[juegosIngresados.dateGame].push(juegosIngresados);
        return acc2;
    }, {});

    const juegosPorFecha3 = props.juegosFinalizados.reduce((acc2, juegosFinalizados) => {
        if (!acc2[juegosFinalizados.dateGame]) {
            acc2[juegosFinalizados.dateGame] = [];
        }
        acc2[juegosFinalizados.dateGame].push(juegosFinalizados);
        return acc2;
    }, {});

    const enviarResultado = (juegoId) => {
        const data = resultados[juegoId];

        if (data?.team1 === undefined || data?.team2 === undefined) {
            alertify.error("Debes ingresar ambos resultados");
            return;
        }
    
        router.post('/quiniela', {
            juego_id: juegoId,
            team1: data?.team1,
            team2: data?.team2,
        }, {
            onSuccess: () => {
                alertify.success("Ingresado correctamente");
            },
            onError: (e) => {
                alertify.error(e.error);
            }
        });
    };

    const actualizarResultado = (juegoId) => {
        const data = resultados[juegoId];
        const scoreTeam1 = Number(data.team1);
        const scoreTeam2 = Number(data.team2);

        if (scoreTeam1 === undefined || scoreTeam2 === undefined) {
            alertify.error("Debes ingresar ambos resultados");
            return;
        }
    
        router.patch(`/quiniela/${juegoId}`, {
            scoreTeam1,
            scoreTeam2,
        }, {
            onSuccess: () => {
                alertify.success("Actualizado correctamente");
            },
            onError: (e) => {
                alertify.error(e.error);
            }
        });
    };

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mi quiniela</h2>}>
            
            <Head title="Mi quiniela"/>

            <div className="pb-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="flex flex-row justify-around pt-5">
                        {tabs.map(tab => {
                            return <button key={tab.id} onClick={() => setActiveTab(tab.id)} className={`px-6 py-2 font-medium rounded-xl border-b-2 ${
                                activeTab === tab.id 
                                ? "bg-uno text-white"
                                : "bg-white text-black"
                            }`}>
                                {tab.label}
                            </button>
                        })}
                    </div>

                    {/* Tab 1 */}
                    {activeTab === "pendientes" && 
                        <div className='mx-3'>
                            {Object.entries(juegosPorFecha).map(([fecha, juegos]) => (
                                <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-uno text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].typeGame}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div> 
                                        <div key={juego.id} className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam1 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex items-center'>
                                                <div className='flex flex-row items-center'>
                                                    <div>
                                                    <input 
                                                        type='number'
                                                        min={0}
                                                        required
                                                        className='w-20 h-[2rem]'
                                                        value={resultados[juego.id]?.team1 || ''}
                                                        onChange={(e) => setResultados({
                                                            ...resultados,
                                                            [juego.id]: {
                                                                ...resultados[juego.id],
                                                                team1: e.target.value
                                                            }
                                                        })}
                                                    />
                                                    </div>

                                                    <div className='mx-2'>:</div>
                                                    
                                                    <div>
                                                        <input 
                                                            type='number'
                                                            min={0}
                                                            required
                                                            className='w-20 h-[2rem]'
                                                            value={resultados[juego.id]?.team2 || ''}
                                                            onChange={(e) => setResultados({
                                                                ...resultados,
                                                                [juego.id]: {
                                                                    ...resultados[juego.id],
                                                                    team2: e.target.value
                                                                }
                                                            })}
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam2 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                            <div 
                                                className='bg-dos text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm cursor-pointer' 
                                                onClick={() => enviarResultado(juego.id)}> Ingresar resultado 
                                            </div> 
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                        </div>
                    }

                    {/* Tab 2 */}
                    {activeTab === "ingresadas" && <div className='mx-3'>
                        {Object.entries(juegosPorFecha2).map(([fecha, juegos]) => (
                            <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-uno text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].typeGame}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div> 
                                        <div key={juego.id} className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam1 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex items-center'>
                                                <div className='flex flex-row items-center'>
                                                    <div>
                                                    {juego.status === "En juego" 
                                                        ? (
                                                           <span>
                                                            {resultados[juego.id]?.team1 ?? juego.scoreTeam1 ?? ''}
                                                           </span>
                                                        ) 
                                                        : (
                                                            <input 
                                                            type='number'
                                                            min={0}
                                                            required
                                                            className='w-20 h-[2rem]'
                                                            value={resultados[juego.id]?.team1 ?? juego.scoreTeam1 ?? ''}
                                                            onChange={(e) => setResultados({
                                                                ...resultados,
                                                                [juego.id]: {
                                                                    ...resultados[juego.id],
                                                                    team2: e.target.value
                                                                }
                                                            })}
                                                        />
                                                    )}
                                                    </div>

                                                    <div className='mx-2'>:</div>
                                                    
                                                    <div>
                                                    {juego.status === "En juego" 
                                                        ? (
                                                           <span>
                                                            {resultados[juego.id]?.team2 ?? juego.scoreTeam2 ?? ''}
                                                           </span>
                                                        ) 
                                                        : (
                                                            <input 
                                                            type='number'
                                                            min={0}
                                                            required
                                                            className='w-20 h-[2rem]'
                                                            value={resultados[juego.id]?.team2 ?? juego.scoreTeam2 ?? ''}
                                                            onChange={(e) => setResultados({
                                                                ...resultados,
                                                                [juego.id]: {
                                                                    ...resultados[juego.id],
                                                                    team2: e.target.value
                                                                }
                                                            })}
                                                        />
                                                    )}
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam2 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                        {juego.status === "En juego" 
                                            ? (
                                                <div className="bg-dos text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm">
                                                    En juego
                                                </div>
                                            ) 
                                            : (
                                                <div 
                                                    className='bg-dos text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm cursor-pointer' 
                                                    onClick={() => actualizarResultado(juego.id)}>
                                                    Actualizar resultado
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                    </div>}
                    
                    {/* Tab 3 */}
                    {activeTab === "finalizados" && <div className='mx-3'>
                        {Object.entries(juegosPorFecha3).map(([fecha, juegos]) => (
                            <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-uno text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].typeGame}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div> 
                                        <div key={juego.id} className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-[40%] '> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam1 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex flex-col items-center flex-1'>
                                            <div className='flex flex-row justify-center w-full'>
                                                    <div className='mx-2'>Resultado</div>
                                                    <div> {resultados[juego.id]?.score1 ?? juego.score1 ?? ''} </div>
                                                    <div className='mx-2'>:</div>
                                                    <div> {resultados[juego.id]?.score2 ?? juego.score2 ?? ''} </div>
                                                </div>

                                                <div className='flex flex-row justify-center w-full'>
                                                    <div className='mx-2'>Quiniela</div>
                                                    <div> {resultados[juego.id]?.team1 ?? juego.scoreTeam1 ?? ''} </div>
                                                    <div className='mx-2'>:</div>
                                                    <div> {resultados[juego.id]?.team2 ?? juego.scoreTeam2 ?? ''} </div>
                                                </div>
                                            </div>
                                            
                                
                                            <div className='flex flex-col items-center w-[40%] '> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenTeam2 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.team2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                            <div className='bg-dos text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm' > 
                                                Puntos: 
                                            </div> 
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                    </div>}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
