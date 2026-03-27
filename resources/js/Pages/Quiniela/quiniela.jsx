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

    if (props.juegosPendientes.length > 0) {
        const juegosPorFecha = props.juegosPendientes.reduce((acc, juegosPendientes) => {
            if (!acc[juegosPendientes.fechaJuego]) {
                acc[juegosPendientes.fechaJuego] = [];
            }
            acc[juegosPendientes.fechaJuego].push(juegosPendientes);
            return acc;
        }, {});
    }
    
    if (props.juegosIngresados.length > 0) {
        const juegosPorFecha2 = props.juegosIngresados.reduce((acc2, juegosIngresados) => {
            if (!acc2[juegosIngresados.fechaJuego]) {
                acc2[juegosIngresados.fechaJuego] = [];
            }
            acc2[juegosIngresados.fechaJuego].push(juegosIngresados);
            return acc2;
        }, {});
    }

    

    if (props.juegosFinalizados.length > 0) {
        const juegosPorFecha3 = props.juegosFinalizados.reduce((acc2, juegosFinalizados) => {
            if (!acc2[juegosFinalizados.fechaJuego]) {
                acc2[juegosFinalizados.fechaJuego] = [];
            }
            acc2[juegosFinalizados.fechaJuego].push(juegosFinalizados);
            return acc2;
        }, {});
    }
    

    const enviarResultado = (juegoId) => {
        const data = resultados[juegoId];

        if (data?.equipo1 === undefined || data?.equipo2 === undefined) {
            alertify.error("Debes ingresar ambos resultados");
            return;
        }
    
        router.post('/quiniela', {
            juego_id: juegoId,
            equipo1: data?.equipo1,
            equipo2: data?.equipo2,
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
        const quinielaEquipo1 = Number(data.equipo1);
        const quinielaEquipo2 = Number(data.equipo2);

        if (quinielaEquipo1 === undefined || quinielaEquipo2 === undefined) {
            alertify.error("Debes ingresar ambos resultados");
            return;
        }
    
        router.patch(`/quiniela/${juegoId}`, {
            quinielaEquipo1,
            quinielaEquipo2,
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
                                ? "bg-azul text-white"
                                : "bg-white text-black"
                            }`}>
                                {tab.label}
                            </button>
                        })}
                    </div>

                    {/* Tab 1 */}
                    {activeTab === "pendientes" && props.juegosPendientes > 0 && 
                        <div className='mx-3'>
                            {Object.entries(juegosPorFecha).map(([fecha, juegos]) => (
                                <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-azul text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].tipoJuego}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div key={juego.id}> 
                                        <div className='flex flex-row mt-4 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenequipo1 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex items-center'>
                                                <div className='flex flex-row items-center'>
                                                <div>
                                                        {juego.estatus.nombre === "En juego" 
                                                            ? (
                                                            <span>
                                                                {resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''}
                                                            </span>
                                                            ) 
                                                            : (
                                                                <input 
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo1: e.target.value
                                                                    }
                                                                })}
                                                            />
                                                        )}
                                                    </div>

                                                    <div className='mx-2'>:</div>
                                                    
                                                    <div>
                                                        {juego.estatus.nombre === "En juego" 
                                                            ? (
                                                            <span>
                                                                {resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''}
                                                            </span>
                                                            ) 
                                                            : (
                                                                <input 
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo2: e.target.value
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
                                                        src={juego.imagenequipo2 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                        {juego.estatus.nombre != "Programado" 
                                            ? (
                                                <div style={{ backgroundColor: juego.estatus.color }} className="text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm">
                                                    {/* spinner */}
                                                    {/* <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>                                                     */}
                                                    {juego.estatus.nombre}
                                                </div>
                                            ) 
                                            : (
                                                <div 
                                                    className='border-2 border-verde text-black bg-white w-fit rounded-lg py-1 px-3 mt-6 text-sm cursor-pointer' 
                                                    onClick={() => actualizarResultado(juego.id)}>
                                                    Actualizar resultado
                                                </div>
                                            )}
                                        </div>
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                        </div>
                    }

                    {/* Tab 2 */}
                    {activeTab === "ingresadas" && props.juegosIngresados > 0 && 
                        <div className='mx-3'>
                        {Object.entries(juegosPorFecha2).map(([fecha, juegos]) => (
                            <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-azul text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].tipoJuego}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div key={juego.id}> 
                                        <div className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-2/5'> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenequipo1 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex items-center'>
                                                <div className='flex flex-row items-center'>
                                                    <div>
                                                        {juego.estatus.nombre === "En juego" 
                                                            ? (
                                                            <span>
                                                                {resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''}
                                                            </span>
                                                            ) 
                                                            : (
                                                                <input 
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo1: e.target.value
                                                                    }
                                                                })}
                                                            />
                                                        )}
                                                    </div>

                                                    <div className='mx-2'>:</div>
                                                    
                                                    <div>
                                                        {juego.estatus.nombre === "En juego" 
                                                            ? (
                                                            <span>
                                                                {resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''}
                                                            </span>
                                                            ) 
                                                            : (
                                                                <input 
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo2: e.target.value
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
                                                        src={juego.imagenequipo2 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                        {juego.estatus.nombre != "Programado" 
                                            ? (
                                                <div style={{ backgroundColor: juego.estatus.color }} className="text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm">
                                                    {/* spinner */}
                                                    {/* <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>                                                     */}
                                                    {juego.estatus.nombre}
                                                </div>
                                            ) 
                                            : (
                                                <div 
                                                    className='border-2 border-verde text-black bg-white w-fit rounded-lg py-1 px-3 mt-2 text-sm cursor-pointer' 
                                                    onClick={() => actualizarResultado(juego.id)}>
                                                    Actualizar resultado
                                                </div>
                                            )}
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                        </div>
                    }
                    
                    {/* Tab 3 */}
                    {activeTab === "finalizados"&& props.juegosFinalizados > 0 && 
                        <div className='mx-3'>
                        {Object.entries(juegosPorFecha3).map(([fecha, juegos]) => (
                            <div key={fecha} className="mt-6">

                                {/* FECHA */}
                                <div className="bg-azul text-white p-2 text-center font-bold flex flex-row justify-between">
                                    <div>
                                        {juegos[0].tipoJuego}
                                    </div>
                                    <div>
                                        {fecha}
                                    </div>
                                </div>
                                
                                {/* PARTIDOS DE ESA FECHA */}
                                {juegos.map((juego) => (
                                    <div key={juego.id} > 
                                        <div className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>
                                
                                            <div className='flex flex-col items-center w-[40%] '> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenequipo1 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo1} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo1 ?? 'Pendiente'}</div>
                                            </div>
                                
                                            <div className='flex flex-col items-center flex-1'>
                                            <div className='flex flex-row justify-center w-full'>
                                                    <div className='mx-2'>Resultado</div>
                                                    <div> {resultados[juego.id]?.resultadoEquipo1 ?? juego.resultadoEquipo1 ?? ''} </div>
                                                    <div className='mx-2'>:</div>
                                                    <div> {resultados[juego.id]?.resultadoEquipo2 ?? juego.resultadoEquipo2 ?? ''} </div>
                                                </div>

                                                <div className='flex flex-row justify-center w-full'>
                                                    <div className='mx-2'>Quiniela</div>
                                                    <div> {resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''} </div>
                                                    <div className='mx-2'>:</div>
                                                    <div> {resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''} </div>
                                                </div>
                                            </div>
                                            
                                
                                            <div className='flex flex-col items-center w-[40%] '> 
                                                <div>
                                                    <img 
                                                        src={juego.imagenequipo2 == null ? 'img/static/pendiente.jpeg' : juego.imagenequipo2} 
                                                        alt="imagen" 
                                                        className='w-10 h-10'
                                                    />
                                                </div>
                                                <div className='text-center'>{juego.equipo2 ?? 'Pendiente'}</div>
                                            </div>
                                
                                        </div>
                                        <div className='mt-[-1rem] flex flex-row justify-center'> 
                                            <div className='bg-verde text-white w-fit rounded-lg py-1 px-3 mt-2 text-sm' > 
                                                Puntos: 
                                            </div> 
                                        </div>
                                    </div>
                                    
                                ))}
                                
                                </div>
                            ))}
                        </div>
                    }
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
