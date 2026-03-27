import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm } from "@inertiajs/react";

export default function FormQuiniela(props) {
    const { data, setData, post, processing, errors } = useForm({
        nombre: "",
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route("quiniela.store"));
    };

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crear Quiniela</h2>}>
            <div className="max-w-md mx-auto mt-10 bg-white p-6 rounded-xl shadow-md">
                <h2 className="text-xl font-semibold mb-4 text-gray-800">
                    Crear Quiniela
                </h2>

                <form onSubmit={handleSubmit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700 mb-1">
                            Nombre de la quiniela
                        </label>

                        <input
                            type="text"
                            value={data.nombre}
                            onChange={(e) => setData("nombre", e.target.value)}
                            className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        />

                        {errors.nombre && (
                            <p className="text-red-500 text-sm mt-1">
                                {errors.nombre}
                            </p>
                        )}
                    </div>

                    <button
                        type="submit"
                        disabled={processing}
                        className="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                    >
                        Crear
                    </button>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}