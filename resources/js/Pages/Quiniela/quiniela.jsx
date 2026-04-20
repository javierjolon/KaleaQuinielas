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
