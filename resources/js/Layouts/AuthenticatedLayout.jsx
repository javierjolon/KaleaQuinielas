import { useState } from 'react';
import Navbar from '@/Components/Navbar';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { usePage } from '@inertiajs/react';

export default function Authenticated({ auth, header, children }) {
    const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);
    const { quinielasHeader = [], quinielaActivaId } = usePage().props;

    return (
        <div className="min-h-screen bg-[#f0f0f0] font-sans">
            <Navbar
                auth={auth}
                quinielasHeader={quinielasHeader}
                quinielaActivaId={quinielaActivaId}
                menuOpen={showingNavigationDropdown}
                onMenuToggle={() => setShowingNavigationDropdown((prev) => !prev)}
            />

            {/* Mobile dropdown */}
            <div className={(showingNavigationDropdown ? 'fixed top-0 left-0 right-0 bottom-0 z-50 bg-white overflow-y-auto' : 'hidden') + ' sm:hidden'}>
                {/* Close button inside overlay */}
                <div className="flex justify-end px-4 py-4">
                    <button
                        onClick={() => setShowingNavigationDropdown(false)}
                        className="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                    >
                        <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                {quinielasHeader.length > 0 && (
                    <div className="px-4 pt-4 pb-3 border-b border-gray-200">
                        <label className="block text-base font-bold text-gray-800 mb-2">Quiniela activa</label>
                        <select
                            value={quinielaActivaId ?? ''}
                            onChange={(e) => {
                                const quinielaId = e.target.value;
                                if (!quinielaId) return;
                                import('@inertiajs/react').then(({ router }) => {
                                    router.post(route('quiniela.seleccionar-activa'), { quinielaId }, { preserveScroll: true });
                                });
                            }}
                            className="w-full border border-gray-300 rounded-md px-2 py-2 text-sm text-gray-700"
                        >
                            {quinielasHeader.map((q) => (
                                <option key={q.id} value={q.id}>{q.nombre}</option>
                            ))}
                        </select>
                    </div>
                )}
                <div className="pt-2 pb-3 space-y-1">
                    <ResponsiveNavLink href={route('dashboard')} active={route().current('dashboard')}>Posiciones</ResponsiveNavLink>
                    <ResponsiveNavLink href={route('quiniela.index')} active={route().current('quiniela.index')}>Quiniela</ResponsiveNavLink>
                    <ResponsiveNavLink href={route('var.index')} active={route().current('var.index')}>VAR</ResponsiveNavLink>
                    <ResponsiveNavLink href={route('quiniela.create')} active={route().current('quiniela.create')}>Crear quiniela</ResponsiveNavLink>
                    <ResponsiveNavLink href="/reglas">Reglas</ResponsiveNavLink>
                </div>
                <div className="pt-4 pb-1 border-t border-gray-200">
                    <div className="px-4">
                        <div className="font-medium text-base text-gray-800">{auth.user.name}</div>
                        <div className="font-medium text-sm text-gray-500">{auth.user.telefono}</div>
                    </div>
                    <div className="mt-3 space-y-1">
                        <ResponsiveNavLink href={route('profile.edit')}>Profile</ResponsiveNavLink>
                        <ResponsiveNavLink method="post" href={route('logout')} as="button">Log Out</ResponsiveNavLink>
                    </div>
                </div>
            </div>

            {header && (
                <header className="bg-white shadow">
                    <div className="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">{header}</div>
                </header>
            )}

            <main>{children}</main>
        </div>
    );
}
