import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PhoneCountryInput from '@/Components/PhoneCountryInput';
import { useForm, router } from "@inertiajs/react";
import { useEffect, useState } from "react";

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

    const paises = props.paises ?? [];
    const defaultCountry = paises[0] ?? { code: '', dial: '' };

    const [localPhone, setLocalPhone] = useState('');
    const [paisCode, setPaisCode] = useState(defaultCountry.code);
    const [dialCode, setDialCode] = useState(defaultCountry.dial);

    const {
        data: dataInvitar,
        setData: setDataInvitar,
        post: postInvitar,
        processing: processingInvitar,
    } = useForm({
        telefono: defaultCountry.dial,
        quinielaId: "",
    });

    const [activeTab, setActiveTab] = useState("crear");

    const quinielasActivas = props.quinielasActivas ?? [];
    const competicionesDisponibles = props.competicionesDisponibles ?? [];
    const usuariosPorQuiniela = props.usuariosPorQuiniela ?? {};

    const eliminarUsuario = (quinielaId, usuarioId) => {
        if (!confirm('¿Eliminar este usuario de la quiniela?')) return;
        router.delete(route('quiniela.eliminar-usuario'), {
            data: { quinielaId, usuarioId },
            preserveScroll: true,
        });
    };

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
        if (!dataCrear.competicion) {
            alertify.error('Debes seleccionar una competición.');
            return;
        }
        postCrear(route("quiniela.store"));
    };

    const handleSubmitInvitar = (e) => {
        e.preventDefault();
        postInvitar(route("quiniela.agregar-usuario"));
    };

    function handleCountryChange(countryCode, dial) {
        setPaisCode(countryCode);
        setDialCode(dial);
        setDataInvitar('telefono', dial + localPhone);
    }

    function handlePhoneChange(value) {
        setLocalPhone(value);
        setDataInvitar('telefono', dialCode + value);
    }

    return (
        <AuthenticatedLayout auth={props.auth} errors={props.errors} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Crear Quiniela</h2>}>
            <div className="max-w-md mx-auto mt-10">

                <div className="flex border-b border-gray-200 mb-6">
                    <button
                        onClick={() => setActiveTab("crear")}
                        className={`px-4 py-2 text-sm font-medium transition border-b-2 -mb-px ${
                            activeTab === "crear"
                                ? "border-blue-600 text-blue-600"
                                : "border-transparent text-gray-500 hover:text-gray-700"
                        }`}
                    >
                        Nueva quiniela
                    </button>
                    <button
                        onClick={() => setActiveTab("agregar")}
                        className={`px-4 py-2 text-sm font-medium transition border-b-2 -mb-px ${
                            activeTab === "agregar"
                                ? "border-blue-600 text-blue-600"
                                : "border-transparent text-gray-500 hover:text-gray-700"
                        }`}
                    >
                        Agregar participante
                    </button>
                </div>

                {activeTab === "crear" && (
                    <div className="bg-white p-6 rounded-xl shadow-md">
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
                                    Competición
                                </label>
                                {!dataCrear.competicion && (
                                    <p className="text-xs text-gray-400 mb-2">Seleccione una competición</p>
                                )}
                                <div className="flex flex-wrap gap-2 mt-1">
                                    {competicionesDisponibles.map((item) => {
                                        const val = `${item.competicion}|${item.season}`;
                                        const selected = `${dataCrear.competicion}|${dataCrear.season}` === val;
                                        return (
                                            <button
                                                key={val}
                                                type="button"
                                                onClick={() => {
                                                    setDataCrear("competicion", item.competicion);
                                                    setDataCrear("season", item.season);
                                                }}
                                                className={`px-4 py-2 rounded-full border text-sm font-semibold uppercase tracking-wide transition ${
                                                    selected
                                                        ? "border-green-500 text-green-600 bg-transparent"
                                                        : "border-gray-300 text-gray-600 bg-transparent hover:border-gray-400"
                                                }`}
                                            >
                                                {item.nombre}
                                            </button>
                                        );
                                    })}
                                </div>
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
                )}

                {activeTab === "agregar" && (
                    <div className="bg-white p-6 rounded-xl shadow-md">
                        <form onSubmit={handleSubmitInvitar} className="space-y-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Selecciona quiniela
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

                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1">
                                    Teléfono del usuario
                                </label>
                                <PhoneCountryInput
                                    countries={paises}
                                    telefono={localPhone}
                                    pais={paisCode}
                                    onTelefonoChange={handlePhoneChange}
                                    onPaisChange={handleCountryChange}
                                    error={props.errors?.telefonoInvitado}
                                    disabled={processingInvitar}
                                />
                            </div>

                            <button
                                type="submit"
                                disabled={processingInvitar}
                                className="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition disabled:opacity-50"
                            >
                                Agregar usuario
                            </button>

                            {dataInvitar.quinielaId && (
                                <div>
                                    <p className="text-sm font-medium text-gray-700 mb-2">Participantes</p>
                                    {(usuariosPorQuiniela[dataInvitar.quinielaId] ?? []).length === 0 ? (
                                        <p className="text-xs text-gray-400">Sin participantes aún.</p>
                                    ) : (
                                        <ul className="space-y-1">
                                            {(usuariosPorQuiniela[dataInvitar.quinielaId] ?? []).map((u) => {
                                                const esAdmin = u.id === props.auth.user.id;
                                                return (
                                                <li key={u.id} className="flex items-center justify-between bg-gray-50 rounded-lg px-3 py-2">
                                                    <span className="text-sm text-gray-700">
                                                        {u.name} <span className="text-gray-400 text-xs">({u.telefono})</span>
                                                        {esAdmin && (
                                                            <span className="ml-2 inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-2 py-0.5 rounded-full">Admin</span>
                                                        )}
                                                    </span>
                                                    {!esAdmin && (
                                                        <button
                                                            type="button"
                                                            onClick={() => eliminarUsuario(parseInt(dataInvitar.quinielaId), u.id)}
                                                            className="text-red-500 hover:text-red-700 text-xs font-semibold ml-3"
                                                        >
                                                            Eliminar
                                                        </button>
                                                    )}
                                                </li>
                                                );
                                            })}
                                        </ul>
                                    )}
                                </div>
                            )}
                        </form>
                    </div>
                )}
            </div>
        </AuthenticatedLayout>
    );
}
