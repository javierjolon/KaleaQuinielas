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


    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mi quiniela</h2>}
        >
            <Head title="Mi quiniela" />


            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <Slider />

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
                            <div key={juego.id} className='flex flex-row mt-2 justify-center rounded-xl bg-white mx-3'>

                                <div className='flex flex-col items-center w-2/5'> 
                                    <div>
                                        <img 
                                            src={juego.imagenTeam1 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam1} 
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
                                            src={juego.imagenTeam2 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam2} 
                                            alt="imagen" 
                                            className='w-10 h-10'
                                        />
                                    </div>
                                    <div>{juego.team2 ?? 'Pendiente'}</div>
                                </div>

                            </div>
                            <div className='mt-[-1rem] flex flex-row justify-center'> 
                                <div className='bg-white w-fit rounded-xl py-1 px-3 text-sm'>{juego.status} </div> 
                            </div>
                        </div>
                    ))}

                </div>

                ))}

                {/* { props.juegos.map((juego) => (
                    
                    <div className='bg-gray-400 flex flex-row mt-5 justify-center'>
                        <div className='flex flex-col items-center w-2/5'> 
                            <div> <img src={juego.imagenTeam1 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam1} alt="imagen" className='w-10 h-10' /> </div>
                            <div> {juego.team1 == null ? 'Pendiente' : juego.team1} </div>
                        </div>

                        <div className='flex flex-col'>
                            <div> {juego.dateGame} </div>
                            <div> {juego.timeGame} </div>
                            <div className='flex flex-row items-center w-1/5'>
                                <div> {juego.score1 == null ? 'Pendiente' : juego.score1} </div>
                                <div> : </div>
                                <div> {juego.score2 == null ? 'Pendiente' : juego.score2} </div>
                            </div>
                        </div>

                        <div className='flex flex-col items-center w-2/5'> 
                            <div> <img src={juego.imagenTeam2 == null ? 'img/static/pendiente.jpeg' : juego.imagenTeam2} alt="imagen" className='w-10 h-10' /> </div>
                            <div> {juego.team2 == null ? 'Pendiente' : juego.team2} </div>
                        </div>
                        
                    </div>
                ))}; */}

                    {/* <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-5 p-6">
                        <div className="font-semibold text-xl text-gray-800 leading-tight mb-5 text-center">
                            <h2>Listado de partidos</h2>
                        </div>
                        <table className="w-full">
                            <thead className='text-left'>
                                <tr>
                                <th> </th>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Puntos</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td className='text-center'>
                                        <span class="material-symbols-outlined text-red-500">arrow_circle_down</span>                                    
                                    </td>
                                    <td>2</td>
                                    <td>Malcolm Lockyer</td>
                                    <td>4</td>
                                </tr>
                                <tr>
                                    <td className='text-center'>
                                    <span class="material-symbols-outlined text-green-500">arrow_circle_up</span>
                                    </td>
                                    <td>3.</td>
                                    <td>The Eagles</td>
                                    <td>8</td>
                                </tr>
                                <tr>
                                    <td className='text-center'>
                                    <span class="material-symbols-outlined text-gray-500">block</span>
                                    </td>
                                    <td>4.</td>
                                    <td>Earth, Wind, and Fire</td>
                                    <td>4</td>
                                </tr>
                            </tbody>
                        </table>
                    </div> */}
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
