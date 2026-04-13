import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';

export default function ForgotPassword({ estatus }) {
    const { data, setData, post, processing, errors } = useForm({
        telefono: '',
    });

    const onHandleChange = (event) => {
        setData(event.target.name, event.target.value);
    };

    const submit = (e) => {
        e.preventDefault();

        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot Password" />

            <div className="mb-4 text-sm text-gray-600">
                Indica el teléfono con el que te registraste. Si existe una cuenta, se enviará un enlace de
                restablecimiento al correo configurado en la aplicación (el administrador puede reenviártelo).
            </div>

            {estatus && <div className="mb-4 font-medium text-sm text-green-600">{estatus}</div>}

            <form onSubmit={submit}>
                <InputLabel htmlFor="telefono" value="Teléfono" />

                <TextInput
                    id="telefono"
                    type="tel"
                    name="telefono"
                    value={data.telefono}
                    className="mt-1 block w-full"
                    isFocused={true}
                    onChange={onHandleChange}
                />

                <InputError message={errors.telefono} className="mt-2" />

                <div className="flex items-center justify-end mt-4">
                    <PrimaryButton className="ml-4" disabled={processing}>
                        Enviar enlace de restablecimiento
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
