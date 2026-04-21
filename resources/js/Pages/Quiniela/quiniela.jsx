import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState } from "react";
import TablaPartidos from '@/Pages/Quiniela/Partials/TablaPartidos';


export default function Quiniela(props) {

    const tabs = [
        { id: "pendientes", label: "Pendientes" },
        { id: "ingresadas", label: "Ingresadas" },
        { id: "finalizados", label: "Finalizados" }
      ];

    const [activeTab, setActiveTab] = useState(tabs[0].id);
    const { quinielaActiva } = usePage().props;

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Quiniela {quinielaActiva?.nombre}</h2>}>
            
            <Head title="Mi quiniela"/>

            {quinielaActiva.id === 0 
                ? <div>{quinielaActiva.nombre}</div>
                : <div className="pb-12">
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

                        {/* Tab 1 */}
                        {activeTab === "pendientes" && (
                            <TablaPartidos listadoJuegos={props.juegosPendientes} textoBoton="Ingresar resultado"/>
                        )}
                       
                        {/* Tab 2 */}
                        {activeTab === "ingresadas" && (
                            <TablaPartidos listadoJuegos={props.juegosIngresados} textoBoton="Actualizar resultado"/>
                        )}
                        
                        {/* Tab 3 */}
                        {activeTab === "finalizados" && (
                            <TablaPartidos listadoJuegos={props.juegosFinalizados} soloLectura={true}/>
                        )}
                    </div>
                </div>
            }

            
        </AuthenticatedLayout>
    );
}
