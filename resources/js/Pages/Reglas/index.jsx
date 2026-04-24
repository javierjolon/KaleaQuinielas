import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

export default function Reglas(props) {
    return (
        <AuthenticatedLayout
            auth={props.auth}
            errors={props.errors}
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Reglas</h2>}
        >
            <div className="max-w-2xl mx-auto mt-10 px-4">
                <div className="bg-white p-6 rounded-xl shadow-md">
                    <h2 className="text-xl font-semibold text-gray-800 mb-4">Reglas de la quiniela</h2>
                    <p className="text-gray-500 text-sm">Próximamente...</p>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
