import { useState } from 'react';
import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, router, usePage } from '@inertiajs/react';

export default function Authenticated({ auth, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const { quinielasHeader = [], quinielaActivaId } = usePage().props;

    const handleChangeQuiniela = (event) => {
        const quinielaId = event.target.value;

        if (!quinielaId) {
            return;
        }

        router.post(route('quiniela.seleccionar-activa'), { quinielaId }, { preserveScroll: true });
    };

    return (
        <div className="min-h-screen bg-gray-100">
            <nav className="bg-white border-b border-gray-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href="/">
                                    <ApplicationLogo className="block h-14 w-auto fill-current" />
                                </Link>
                            </div>

                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <NavLink href={route('dashboard')} active={route().current('dashboard')}>
                                    Tabla de posiciones
                                </NavLink>
                                <NavLink href={route('quiniela.index')} active={route().current('quiniela.index')}>
                                    Quiniela
                                </NavLink>
                                <NavLink href={route('var.index')} active={route().current('var.index')}>
                                    VAR
                                </NavLink>
                                <NavLink href={route('quiniela.create')} active={route().current('quiniela.create')}>
                                    Crear quiniela
                                </NavLink>
                                <NavLink href={route('reglas.index')} active={route().current('reglas.index')}>
                                    Reglas
                                </NavLink>
                            </div>
                        </div>

                        {header && (
                            <header className="flex md:hidden bg-white shadow text-center">
                                <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
                            </header>
                        )}

                        <div className="hidden sm:flex sm:items-center sm:ml-6 gap-3">
                            {quinielasHeader.length > 0 && (
                                <select
                                    value={quinielaActivaId ?? ''}
                                    onChange={handleChangeQuiniela}
                                    className="border border-gray-300 rounded-md px-2 py-1 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    {quinielasHeader.map((quiniela) => (
                                        <option key={quiniela.id} value={quiniela.id}>
                                            {quiniela.nombre}
                                        </option>
                                    ))}
                                </select>
                            )}

                            <div className="ml-3 relative">
                                <Dropdown>
                                    <Dropdown.Trigger>
                                        <span className="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                className="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150"
                                            >
                                                {auth.user.name}

                                                <svg
                                                    className="ml-2 -mr-0.5 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fillRule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clipRule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </Dropdown.Trigger>

                                    <Dropdown.Content>
                                        <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                        <Dropdown.Link href={route('logout')} method="post" as="button">
                                            Log Out
                                        </Dropdown.Link>
                                    </Dropdown.Content>
                                </Dropdown>
                            </div>
                        </div>

                        <div className="-mr-2 flex items-center sm:hidden">
                            <button
                                onClick={() => setShowingNavigationDropdown((previousState) => !previousState)}
                                className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        className={!showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        className={showingNavigationDropdown ? 'inline-flex' : 'hidden'}
                                        strokeLinecap="round"
                                        strokeLinejoin="round"
                                        strokeWidth="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div className={(showingNavigationDropdown ? 'fixed top-16 left-0 right-0 bottom-0 z-50 bg-white overflow-y-auto' : 'hidden') + ' sm:hidden'}>
                    {quinielasHeader.length > 0 && (
                        <div className="px-4 pt-4 pb-3 border-b border-gray-200">
                            <label className="block text-base font-bold text-gray-800 mb-2">Quiniela activa</label>
                            <select
                                value={quinielaActivaId ?? ''}
                                onChange={handleChangeQuiniela}
                                className="w-full border border-gray-300 rounded-md px-2 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                                {quinielasHeader.map((quiniela) => (
                                    <option key={quiniela.id} value={quiniela.id}>
                                        {quiniela.nombre}
                                    </option>
                                ))}
                            </select>
                        </div>
                    )}

                    <div className="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>
                            Tabla de posiciones
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('quiniela.index')} active={route().current('quiniela.index')}>
                            Quiniela
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('var.index')} active={route().current('var.index')}>
                            VAR
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('quiniela.create')} active={route().current('quiniela.create')}>
                            Crear quiniela
                        </ResponsiveNavLink>
                        <ResponsiveNavLink href={route('reglas.index')} active={route().current('reglas.index')}>
                            Reglas
                        </ResponsiveNavLink>
                    </div>

                    <div className="pt-4 pb-1 border-t border-gray-200">
                        <div className="px-4">
                            <div className="font-medium text-base text-gray-800">{auth.user.name}</div>
                            <div className="font-medium text-sm text-gray-500">{auth.user.telefono}</div>
                        </div>

                        <div className="mt-3 space-y-1">
                            <ResponsiveNavLink href={route('profile.edit')}>Profile</ResponsiveNavLink>
                            <ResponsiveNavLink method="post" href={route('logout')} as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="hidden md:flex bg-white shadow text-center">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main className='bg-[#e5e7eb]'>{children}</main>
        </div>
    );
}
