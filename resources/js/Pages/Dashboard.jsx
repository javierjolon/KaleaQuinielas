import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Slider from '@/Components/Slider';


export default function Dashboard(props) {
    const usuariosQuiniela = props.usuariosQuiniela ?? [];
    const nombreQuiniela = props.quinielaActiva?.nombre ?? "Sin quiniela activa";
    const posicionActual = usuariosQuiniela.findIndex((usuario) => usuario.id === props.auth.user.id) + 1;

    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mi posicion: {posicionActual > 0 ? `${posicionActual} lugar` : "-"}</h2>}
        >
            <Head title="Home" />


            <div className="py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-5 p-6">
                        <div className="font-semibold text-xl text-gray-800 leading-tight mb-5 text-center">
                            <h2>Tabla de posiciones</h2>
                            <p className="text-sm text-gray-600 mt-2">Quiniela: {nombreQuiniela}</p>
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
                                {usuariosQuiniela.length === 0 ? (
                                    <tr>
                                        <td colSpan="4" className="py-4 text-center text-gray-500">
                                            No hay usuarios en la quiniela seleccionada.
                                        </td>
                                    </tr>
                                ) : (
                                    usuariosQuiniela.map((usuario, index) => (
                                        <tr
                                            key={usuario.id}
                                            className={usuario.id === props.auth.user.id ? "bg-blue-50 font-semibold" : ""}
                                        >
                                            <td className='text-center'>
                                                {usuario.subeBaja === 'b' ? (
                                                    <span className="material-symbols-outlined text-red-500">arrow_circle_down</span>
                                                ) : usuario.subeBaja === 's' ? (
                                                    <span className="material-symbols-outlined text-green-500">arrow_circle_up</span>
                                                ) : (
                                                    <span className="material-symbols-outlined text-gray-500">block</span>
                                                )}
                                            </td>
                                            <td>{index + 1}</td>
                                            <td>{usuario.name}</td>
                                            <td>{usuario.puntosAcumulados ?? 0}</td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
