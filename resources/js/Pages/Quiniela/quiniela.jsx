import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, usePage } from '@inertiajs/react';
import { useState, useEffect } from "react";
import TablaPartidos from '@/Pages/Quiniela/Partials/TablaPartidos';


export default function Quiniela(props) {

    const tabs = [
        { id: "ingresar", label: "Ingresar" },
        { id: "finalizados", label: "Finalizados" }
    ];

    const juegosIngresar = (() => {
        const result = { ...props.juegosPendientes };
        for (const [fecha, juegos] of Object.entries(props.juegosIngresados)) {
            result[fecha] = [...(result[fecha] ?? []), ...juegos];
        }
        const parseFecha = (k) => {
            const [d, m, y] = k.split('-');
            return new Date(`${y}-${m}-${d}`);
        };
        const sorted = {};
        Object.keys(result)
            .sort((a, b) => parseFecha(a) - parseFecha(b))
            .forEach(k => {
                sorted[k] = result[k].sort((a, b) =>
                    (a.horaJuego ?? '').localeCompare(b.horaJuego ?? '')
                );
            });
        return sorted;
    })();

    const [activeTab, setActiveTab] = useState(tabs[0].id);
    const { quinielaActiva } = usePage().props;

    useEffect(() => {
        const interval = setInterval(() => {
            router.reload({ only: ['juegosPendientes', 'juegosIngresados', 'juegosFinalizados'] });
        }, 30000);
        return () => clearInterval(interval);
    }, []);

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight text-center w-full">Quiniela {quinielaActiva?.nombre}</h2>}>
            
            <Head title="Mi quiniela"/>

            {(!quinielaActiva || quinielaActiva.id === 0)
                ? <div>{quinielaActiva?.nombre ?? 'No tienes una quiniela activa'}</div>
                : <div className="pb-12 m-3 sm:m-0">
                    <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
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

                        {activeTab === "ingresar" && (
                            <TablaPartidos listadoJuegos={juegosIngresar} quinielaId={quinielaActiva?.id}/>
                        )}

                        {activeTab === "finalizados" && (
                            <TablaPartidos listadoJuegos={props.juegosFinalizados} soloLectura={true} quinielaId={quinielaActiva?.id}/>
                        )}
                    </div>
                </div>
            }

            
        </AuthenticatedLayout>
    );
}
