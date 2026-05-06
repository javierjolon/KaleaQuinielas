import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PublicLayout from '@/Layouts/PublicLayout';

export default function Reglas({ auth, errors, reglas = [] }) {
    const content = (
        <div className="max-w-[55rem] mx-auto mt-10 px-4 pb-10">
            <div className="bg-white p-6 rounded-xl shadow-md">
                <h2 className="text-xl font-semibold text-gray-800 mb-6">Reglas de la quiniela</h2>
                {reglas.length === 0 ? (
                    <p className="text-gray-500 text-sm">No hay reglas publicadas aún.</p>
                ) : (
                    <div className="space-y-6">
                        {reglas.map((grupo) => (
                            <div key={grupo.id}>
                                <h3 className="text-base font-bold text-gray-700 mb-2 border-b pb-1">
                                    {grupo.titulo}
                                </h3>
                                <ul className="space-y-2 mt-2">
                                    {grupo.items.map((item, i) => (
                                        <li key={item.id} className="flex gap-3">
                                            <span className="flex-shrink-0 w-6 h-6 rounded-full bg-green-100 text-green-700 font-bold text-xs flex items-center justify-center">
                                                {i + 1}
                                            </span>
                                            <p className="text-gray-600 text-sm">{item.descripcion}</p>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </div>
    );

    if (auth?.user) {
        return (
            <AuthenticatedLayout
                auth={auth}
                errors={errors}
                header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Reglas</h2>}
            >
                {content}
            </AuthenticatedLayout>
        );
    }

    return <PublicLayout auth={auth}>{content}</PublicLayout>;
}
