import { router } from '@inertiajs/react';
import { useState } from 'react';


export default function TablaPartidos(props) {
    const [resultados, setResultados] = useState({});
    const soloLectura = props.soloLectura === true;

   

    const actualizarResultado = (juegoId) => {
        const data = resultados[juegoId];
        const raw1 = data?.equipo1;
        const raw2 = data?.equipo2;

        if (raw1 === undefined || raw1 === null || raw1 === '' || raw2 === undefined || raw2 === null || raw2 === '') {
            alertify.error("Debes ingresar ambos resultados");
            return;
        }

        const quinielaEquipo1 = Number(raw1);
        const quinielaEquipo2 = Number(raw2);

        if (isNaN(quinielaEquipo1) || isNaN(quinielaEquipo2)) {
            alertify.error("Los resultados deben ser números válidos");
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

    return(
        <div>
            { Object.keys(props.listadoJuegos).length > 0 && 
                <div className='mx-3'>
                    {Object.entries(props.listadoJuegos).map(([fecha, juegos]) => (
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
                            
                            {juegos.map((juego) => (
                                <div key={juego.id}> 
                                    {(() => {
                                        const tieneMarcador = juego.quinielaEquipo1 !== null && juego.quinielaEquipo2 !== null;
                                        const mostrarInputs = !soloLectura && juego.estatusQuiniela.nombre !== "Bloqueado" && juego.estatusQuiniela.nombre !== "Finalizado" && juego.estatusQuiniela.nombre !== "En juego" && juego.estatusJuego.nombre !== "En juego" && juego.estatusJuego.nombre !== "Finalizado" && juego.estatusJuego.nombre !== "Bloqueado";

                                        return (
                                    <div className='flex flex-row mt-4 justify-center rounded-xl bg-white mx-3 p-2'>
                            
                                        <div className='flex flex-col items-center w-2/5 justify-center'> 
                                            <div>
                                                <img 
                                                    src={juego.imagenEquipo1 == null ? 'img/static/pendiente.jpeg' : juego.imagenEquipo1} 
                                                    alt="imagen" 
                                                    className='w-10 h-10'
                                                />
                                            </div>
                                            <div className='text-center'>{juego.equipo1 ?? 'Pendiente'}</div>
                                        </div>
                            
                                        <div className='flex items-center'>
                                            {soloLectura || juego.estatusJuego.nombre === "En juego" ? (
                                                <div className='flex flex-col items-center gap-1'>
                                                    <div className='flex flex-row items-center text-lg font-bold'>
                                                        <span>{juego.resultadoEquipo1 ?? '-'}</span>
                                                        <span className='mx-2'>:</span>
                                                        <span>{juego.resultadoEquipo2 ?? '-'}</span>
                                                    </div>
                                                    {tieneMarcador ? (
                                                        <div className='flex flex-row items-center text-sm text-gray-500'>
                                                            <span>{juego.quinielaEquipo1}</span>
                                                            <span className='mx-1'>:</span>
                                                            <span>{juego.quinielaEquipo2}</span>
                                                        </div>
                                                    ) : (
                                                        <span className='text-red-600 text-sm font-semibold'>No válido</span>
                                                    )}
                                                    <div className='text-xs font-semibold text-azul'>
                                                        {juego.puntosXjuego ?? 0} pts
                                                    </div>
                                                </div>
                                            ) : (
                                                <div className='flex flex-row items-center'>
                                                    <div>
                                                        {mostrarInputs ? (
                                                            <input
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ""}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo1: e.target.value
                                                                    }
                                                                })}
                                                            />
                                                        ) : (
                                                            <span>{resultados[juego.id]?.equipo1 ?? juego.quinielaEquipo1 ?? ''}</span>
                                                        )}
                                                    </div>
                                                    <div className='mx-2'>:</div>
                                                    <div>
                                                        {mostrarInputs ? (
                                                            <input
                                                                type='number'
                                                                min={0}
                                                                required
                                                                className='w-20 h-[2rem]'
                                                                value={resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ""}
                                                                onChange={(e) => setResultados({
                                                                    ...resultados,
                                                                    [juego.id]: {
                                                                        ...resultados[juego.id],
                                                                        equipo2: e.target.value
                                                                    }
                                                                })}
                                                            />
                                                        ) : (
                                                            <span>{resultados[juego.id]?.equipo2 ?? juego.quinielaEquipo2 ?? ''}</span>
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                            
                                        <div className='flex flex-col items-center w-2/5 justify-center'> 
                                            <div>
                                                <img 
                                                    src={juego.imagenEquipo2 == null ? 'img/static/pendiente.jpeg' : juego.imagenEquipo2} 
                                                    alt="imagen" 
                                                    className='w-10 h-10'
                                                />
                                            </div>
                                            <div className='text-center'>{juego.equipo2 ?? 'Pendiente'}</div>
                                        </div>
                            
                                    </div>
                                        );
                                    })()}
                                    <div className='mt-[-0.5rem] flex flex-row justify-center'> 
                                        {soloLectura ? (
                                            <div style={{ backgroundColor: juego.estatusQuiniela.color }} className="text-white w-fit rounded-lg py-1 px-3 text-sm">
                                                {juego.estatusQuiniela.nombre}
                                            </div>
                                        ) : juego.estatusJuego.nombre == "Bloqueado" || juego.estatusJuego.nombre == "Finalizado" || juego.estatusJuego.nombre == "En juego"
                                        ? (
                                            <div style={{ backgroundColor: juego.estatusJuego.color }} className="text-white w-fit rounded-lg py-1 px-3 text-sm">
                                                {/* spinner */}
                                                {/* <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>                                                     */}
                                                {juego.estatusJuego.nombre}
                                            </div>
                                        ) 
                                        : (
                                            <div 
                                                className='border-2 border-verde text-black bg-white w-fit rounded-lg py-1 px-3 text-sm cursor-pointer' 
                                                onClick={() => actualizarResultado(juego.id)}>
                                                {props.textoBoton}
                                            </div>
                                        )}
                                    </div>
                                </div>
                                
                            ))}
                        
                        </div>
                    ))}
                </div>
            }
        </div>
    );

}