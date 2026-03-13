import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Slider from '@/Components/Slider';


export default function Quiniela(props) {

    const juegosPorFecha = props.juegos.reduce((acc, juego) => {
        console.log(juego);
        if (!acc[juego.dateGame]) {
            acc[juego.dateGame] = [];
        }
        acc[juego.dateGame].push(juego);
        return acc;
    }, {});

    const statusColors = {
        'Programado' : "bg-[#BFC9D1] text-black",
        'En juego' : "bg-[#237227] text-white",
        'Medio tiempo' : "bg-white text-black",
        'Finalizdo' : "bg-[#C00707] text-white",
        'Suspendido' : "bg-white text-black",
        'Pospuesto' : "bg-white text-black",
        'Cancelado' : "bg-white text-black",
        'Gano por default' : "bg-white text-black",
    };

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Juegos programados</h2>}
        >
            <Head title="Mi quiniela" />


            <div className="pb-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                {/* <Slider /> */}

                {Object.entries(juegosPorFecha).map(([fecha, juegos]) => (

                <div key={fecha} className="mt-6">

                    {/* FECHA */}
                    <div className="bg-[#d2ee7c] text-[#184530] p-2 text-center font-bold flex flex-row justify-around">
                        <div>
                            {fecha}
                        </div>
                        <div>
                         {juegos[0].typeGame}
                        </div>
                    </div>

                    {/* PARTIDOS DE ESA FECHA */}
                    {juegos.map((juego) => (
                        <div> 
                            <div key={juego.id} className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3 py-1'>

                                <div className='flex flex-col items-center w-2/5'> 
                                    <div>
                                        <img 
                                            src={juego.imagenTeam1 == null ? 'img/static/balon.jpeg' : juego.imagenTeam1} 
                                            alt="imagen" 
                                            className='w-10 h-10'
                                        />
                                    </div>
                                    <div>{juego.team1 ?? 'Pendiente'}</div>
                                </div>

                                <div className='flex flex-col items-center'>
                                    <div>{juego.timeGame}</div>

                                    <div className='flex flex-row items-center'>
                                        <div>{juego.score1 ?? 'Pendiente'}</div>
                                        <div className='mx-1'>:</div>
                                        <div>{juego.score2 ?? 'Pendiente'}</div>
                                    </div>
                                </div>

                                <div className='flex flex-col items-center w-2/5'> 
                                    <div>
                                        <img 
                                            src={juego.imagenTeam2 == null ? 'img/static/balon.jpeg' : juego.imagenTeam2} 
                                            alt="imagen" 
                                            className='w-10 h-10'
                                        />
                                    </div>
                                    <div>{juego.team2 ?? 'Pendiente'}</div>
                                </div>

                            </div>
                            <div className='mt-[-1rem] flex flex-row justify-center'> 
                                <div className={`w-fit rounded-xl py-1 px-3 text-sm ${statusColors[juego.status] || 'bg-white'}`}>{juego.status} </div> 
                            </div>
                        </div>
                    ))}

                </div>

                ))}

                </div>
            </div>

        </AuthenticatedLayout>
    );
}
