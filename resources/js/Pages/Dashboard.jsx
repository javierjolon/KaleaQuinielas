import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Slider from '@/Components/Slider';


function PodioCard({ usuario, posicion, esMio }) {
    const icons = {
        1: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" className="w-8 h-8 fill-amber-400 absolute top-2 right-2"><path d="M280-120v-80h160v-124q-49-11-87.5-41.5T296-442q-75-9-125.5-65.5T120-640v-40q0-33 23.5-56.5T200-760h80v-80h400v80h80q33 0 56.5 23.5T840-680v40q0 76-50.5 132.5T664-442q-18 46-56.5 76.5T520-324v124h160v80H280Zm0-408v-152h-80v40q0 38 22 68.5t58 43.5Zm285 93q35-35 35-85v-240H360v240q0 50 35 85t85 35q50 0 85-35Zm115-93q36-13 58-43.5t22-68.5v-40h-80v152Zm-200-52Z"/></svg>,
        2: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" className="w-8 h-8 fill-gray-400 absolute top-2 right-2"><path d="m387-412 35-114-92-74h114l36-112 36 112h114l-93 74 35 114-92-71-93 71ZM240-40v-309q-38-42-59-96t-21-115q0-134 93-227t227-93q134 0 227 93t93 227q0 61-21 115t-59 96v309l-240-80-240 80Zm410-350q70-70 70-170t-70-170q-70-70-170-70t-170 70q-70 70-70 170t70 170q70 70 170 70t170-70ZM320-159l160-41 160 41v-124q-35 20-75.5 31.5T480-240q-44 0-84.5-11.5T320-283v124Zm160-62Z"/></svg>,
        3: <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 -960 960 960" className="w-8 h-8 fill-orange-400 absolute top-2 right-2"><path d="M395-475q-35-35-35-85t35-85q35-35 85-35t85 35q35 35 35 85t-35 85q-35 35-85 35t-85-35ZM240-40v-309q-38-42-59-96t-21-115q0-134 93-227t227-93q134 0 227 93t93 227q0 61-21 115t-59 96v309l-240-80-240 80Zm410-350q70-70 70-170t-70-170q-70-70-170-70t-170 70q-70 70-70 170t70 170q70 70 170 70t170-70ZM320-159l160-41 160 41v-124q-35 20-75.5 31.5T480-240q-44 0-84.5-11.5T320-283v124Zm160-62Z"/></svg>,
    };
    const cfg = {
        1: { border: 'border-amber-400',  badge: 'bg-amber-400',  pts: 'text-amber-600',  label: '1°', mt: 'mt-0'  },
        2: { border: 'border-gray-300',   badge: 'bg-gray-400',   pts: 'text-gray-600',   label: '2°', mt: 'mt-8'  },
        3: { border: 'border-orange-300', badge: 'bg-orange-400', pts: 'text-orange-600', label: '3°', mt: 'mt-16' },
    }[posicion];
    return (
        <div className={`relative flex flex-col items-center rounded-xl border-2 ${cfg.border} bg-white px-3 pt-11 pb-3 flex-1 ${cfg.mt}`}>
            {icons[posicion]}
            <span className={`${cfg.badge} text-white text-xs font-bold px-2 py-0.5 rounded-full absolute top-2 left-2 sm:left-1/2 sm:-translate-x-1/2`}>{cfg.label}</span>
            <span className={`text-xs font-bold text-center w-full line-clamp-2 min-h-[2rem] leading-tight ${esMio ? 'text-blue-600' : 'text-gray-800'}`}>{usuario.name}</span>
            <span className={`text-xl font-bold ${cfg.pts} mt-1`}>{usuario.puntosAcumulados ?? 0}</span>
            <span className="text-xs text-gray-400">pts</span>
        </div>
    );
}

export default function Dashboard(props) {
    const usuariosQuiniela = props.usuariosQuiniela ?? [];
    const nombreQuiniela = props.quinielaActiva?.nombre ?? "Sin quiniela activa";
    const posicionActual = usuariosQuiniela.findIndex((usuario) => usuario.id === props.auth.user.id) + 1;
    const top3 = usuariosQuiniela.slice(0, 3);
    const mostrarPodio = top3.length === 3
        && top3[0].puntosAcumulados !== top3[1].puntosAcumulados
        && top3[1].puntosAcumulados !== top3[2].puntosAcumulados;
    const tablaUsuarios = mostrarPodio ? usuariosQuiniela.slice(3) : usuariosQuiniela;

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight text-center w-full">Mi posicion: {posicionActual > 0 ? `${posicionActual} lugar` : "-"}</h2>}
        >
            <Head title="Home" />


            <div className="m-3 sm:m-0">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-5 p-6">
                        <div className="font-semibold text-xl text-gray-800 leading-tight mb-5 text-center">
                            <h2>Tabla de posiciones</h2>
                            <p className="text-sm text-gray-600 mt-2">Quiniela: {nombreQuiniela}</p>
                        </div>

                        {mostrarPodio && (
                            <div className="flex flex-row items-start justify-center gap-3 mb-6">
                                <PodioCard usuario={top3[1]} posicion={2} esMio={top3[1].id === props.auth.user.id} />
                                <PodioCard usuario={top3[0]} posicion={1} esMio={top3[0].id === props.auth.user.id} />
                                <PodioCard usuario={top3[2]} posicion={3} esMio={top3[2].id === props.auth.user.id} />
                            </div>
                        )}

                        {tablaUsuarios.length > 0 && (
                        <table className="w-full">
                            <thead className='text-center'>
                                <tr>
                                <th> </th>
                                <th>#</th>
                                <th className="text-left">Nombre</th>
                                <th>Puntos</th>
                                </tr>
                            </thead>
                            <tbody>
                                {usuariosQuiniela.length === 0 ? (
                                    <tr>
                                        <td colSpan="4" className="py-4 text-center text-gray-500">
                                            No hay usuarios en la quiniela seleccionada.
                                        </td>
                                    </tr>
                                ) : (
                                    tablaUsuarios.map((usuario, index) => {
                                        const indexReal = mostrarPodio ? index + 3 : index;
                                        const esMio = usuario.id === props.auth.user.id;
                                        const subeBajaCelda = (
                                            <td className="text-center">
                                                {usuario.subeBaja === 'b' ? (
                                                    <span className="material-symbols-outlined text-red-500">arrow_circle_down</span>
                                                ) : usuario.subeBaja === 's' ? (
                                                    <span className="material-symbols-outlined text-green-500">arrow_circle_up</span>
                                                ) : (
                                                    <span className="material-symbols-outlined text-gray-500">block</span>
                                                )}
                                            </td>
                                        );
                                        return (
                                            <tr key={usuario.id}>
                                                {subeBajaCelda}
                                                <td className="text-center py-2">
                                                    {esMio
                                                        ? <span className="text-blue-600 font-bold text-base">{indexReal + 1}</span>
                                                        : indexReal + 1
                                                    }
                                                </td>
                                                <td className={`text-left py-2 ${esMio ? 'text-blue-600 font-semibold' : ''}`}>
                                                    {usuario.name}
                                                </td>
                                                <td className={`text-center py-2 ${esMio ? 'text-blue-600 font-bold' : ''}`}>
                                                    {usuario.puntosAcumulados ?? 0}
                                                </td>
                                            </tr>
                                        );
                                    })
                                )}
                            </tbody>
                        </table>
                        )}
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
