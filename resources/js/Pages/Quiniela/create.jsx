import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { useForm } from "@inertiajs/react";
import { useEffect } from "react";

export default function FormQuiniela(props) {
    const {
        data: dataCrear,
        setData: setDataCrear,
        post: postCrear,
        processing: processingCrear,
    } = useForm({
        nombre: "",
        competicion: "",
        season: "",
    });

    const {
        data: dataInvitar,
        setData: setDataInvitar,
        post: postInvitar,
        processing: processingInvitar,
    } = useForm({
        telefono: "",
        quinielaId: "",
    });

    const quinielasActivas = props.quinielasActivas ?? [];
    const competicionesDisponibles = props.competicionesDisponibles ?? [];

    useEffect(() => {
        if (props.estatus) alertify.success(props.estatus);
    }, [props.estatus]);

    useEffect(() => {
        const err = props.errors?.nombre || props.errors?.competicion || props.errors?.season
            || props.errors?.telefono || props.errors?.quinielaId || props.errors?.telefonoInvitado;
        if (err) alertify.error(err);
    }, [props.errors]);

    const handleSubmitCrear = (e) => {
        e.preventDefault();
        postCrear(route("quiniela.store"));
    };

    const handleSubmitInvitar = (e) => {
        e.preventDefault();
        postInvitar(route("quiniela.agregar-usuario"));
    };

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crear Quiniela</h2>}>
            <div className="max-w-md mx-auto mt-10 space-y-6">

                <div className="bg-white p-6 rounded-xl shadow-md">
                    <h2 className="text-xl font-semibold mb-4 text-gray-800">
                        Crear Quiniela
                    </h2>

                    <form onSubmit={handleSubmitCrear} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Nombre de la quiniela
                            </label>

                            <input
                                type="text"
                                value={dataCrear.nombre}
                                onChange={(e) => setDataCrear("nombre", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />

                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Competicion
                            </label>
                            <select
                                value={`${dataCrear.competicion}|${dataCrear.season}`}
                                onChange={(e) => {
                                    const [competicion, season] = e.target.value.split("|");
                                    setDataCrear("competicion", competicion ?? "");
                                    setDataCrear("season", season ?? "");
                                }}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="|">Selecciona una competicion</option>
                                {competicionesDisponibles.map((item) => (
                                    <option
                                        key={`${item.competicion}-${item.season}`}
                                        value={`${item.competicion}|${item.season}`}
                                    >
                                        {item.nombre} - {item.season}
                                    </option>
                                ))}
                            </select>

                        </div>

                        <button
                            type="submit"
                            disabled={processingCrear}
                            className="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
                        >
                            Crear
                        </button>
                    </form>
                </div>

                <div className="bg-white p-6 rounded-xl shadow-md">
                    <h2 className="text-xl font-semibold mb-4 text-gray-800">
                        Agregar usuario a quiniela
                    </h2>

                    <form onSubmit={handleSubmitInvitar} className="space-y-4">
                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Telefono del usuario
                            </label>
                            <input
                                type="tel"
                                value={dataInvitar.telefono}
                                onChange={(e) => setDataInvitar("telefono", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            />
                        </div>

                        <div>
                            <label className="block text-sm font-medium text-gray-700 mb-1">
                                Quiniela activa
                            </label>
                            <select
                                value={dataInvitar.quinielaId}
                                onChange={(e) => setDataInvitar("quinielaId", e.target.value)}
                                className="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                <option value="">Selecciona una quiniela</option>
                                {quinielasActivas.map((quiniela) => (
                                    <option key={quiniela.id} value={quiniela.id}>
                                        {quiniela.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>


                        <button
                            type="submit"
                            disabled={processingInvitar}
                            className="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                        >
                            Agregar usuario
                        </button>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}