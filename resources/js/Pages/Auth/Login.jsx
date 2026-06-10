import { useEffect, useRef, useState } from 'react';
import Checkbox from '@/Components/Checkbox';
import GuestLayout from '@/Layouts/GuestLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import PhoneCountryInput from '@/Components/PhoneCountryInput';
import { Head, Link, useForm } from '@inertiajs/react';
import { Turnstile } from '@marsidev/react-turnstile';

export default function Login({ estatus, canResetPassword, paises = [], turnstileSiteKey = '' }) {
    const defaultCountry = paises[0] ?? { code: '', dial: '' };

    const [localPhone, setLocalPhone] = useState('');
    const [pais, setPais] = useState(defaultCountry.code);
    const [dialCode, setDialCode] = useState(defaultCountry.dial);
    const turnstileRef = useRef(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        telefono: defaultCountry.dial,
        password: '',
        remember: '',
        cf_turnstile_response: '',
    });

    useEffect(() => {
        return () => {
            reset('password');
        };
    }, []);

    function handleCountryChange(countryCode, dial) {
        setPais(countryCode);
        setDialCode(dial);
        setData('telefono', dial + localPhone);
    }

    function handlePhoneChange(value) {
        setLocalPhone(value);
        setData('telefono', dialCode + value);
    }

    const handleOnChange = (event) => {
        setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
    };

    const submit = (e) => {
        e.preventDefault();
        post(route('login'), {
            onError: () => turnstileRef.current?.reset(),
        });
    };

    return (
        <GuestLayout>
            <Head title="Iniciar sesión" />

            {estatus && <div className="mb-4 font-medium text-sm text-green-600">{estatus}</div>}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="telefono" value="Teléfono" />

                    <PhoneCountryInput
                        countries={paises}
                        telefono={localPhone}
                        pais={pais}
                        onTelefonoChange={handlePhoneChange}
                        onPaisChange={handleCountryChange}
                        error={errors.telefono}
                        disabled={processing}
                    />

                    <InputError message={errors.telefono} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Contraseña" />

                    <input
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm px-3 py-2 text-sm focus:ring-indigo-500 focus:border-indigo-500"
                        autoComplete="current-password"
                        onChange={handleOnChange}
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="block mt-4">
                    <label className="flex items-center">
                        <Checkbox name="remember" value={data.remember} onChange={handleOnChange} />
                        <span className="ml-2 text-sm text-gray-600">Recordarme</span>
                    </label>
                </div>

                <div className="mt-4">
                    <Turnstile
                        ref={turnstileRef}
                        siteKey={turnstileSiteKey}
                        onSuccess={(token) => setData('cf_turnstile_response', token)}
                        onExpire={() => setData('cf_turnstile_response', '')}
                        options={{ theme: 'light' }}
                    />
                    <InputError message={errors.cf_turnstile_response} className="mt-2" />
                </div>

                <div className="flex items-center justify-end mt-4">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        >
                            ¿Olvidaste tu contraseña?
                        </Link>
                    )}

                    <PrimaryButton className="ml-4" disabled={processing || !data.cf_turnstile_response}>
                        Ingresar
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
