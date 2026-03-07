import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Slider from '@/Components/Slider';


export default function Dashboard(props) {
    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Mi posición: 14 lugar</h2>}
        >
            <Head title="Home" />


            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">

                
                <Slider />

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-5 p-6">
                        <div className="font-semibold text-xl text-gray-800 leading-tight mb-5 text-center">
                            <h2>Tabla de posiciones</h2>
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
                    </div>
                </div>
            </div>

        </AuthenticatedLayout>
    );
}
