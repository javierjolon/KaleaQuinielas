import { Link, router } from '@inertiajs/react';
import Dropdown from '@/Components/Dropdown';

export default function Navbar({ auth, quinielasHeader = [], quinielaActivaId, menuOpen, onMenuToggle }) {
    const handleChangeQuiniela = (event) => {
        const quinielaId = event.target.value;
        if (!quinielaId) return;
        router.post(route('quiniela.seleccionar-activa'), { quinielaId }, { preserveScroll: true });
    };

    return (
        <nav className="flex items-center justify-between px-8 py-2 bg-[#f0f0f0]">
            {/* Logo */}
            <div className="flex items-center">
                <Link href="/">
                    <img src="/images/kingol-logo.png" alt="Kingol" className="h-20 w-auto sm:h-32 object-contain" />
                </Link>
            </div>

            {/* Center links */}
            <div className="hidden md:flex items-center gap-8">
                {!auth?.user && <Link href="/" className="text-gray-500 hover:text-gray-700 font-medium">Home</Link>}
                {auth?.user ? (
                    <>
                        <Link href={route('dashboard')} className="text-gray-500 hover:text-gray-700 font-medium">Posiciones</Link>
                        <Link href={route('quiniela.index')} className="text-gray-500 hover:text-gray-700 font-medium">Quiniela</Link>
                        <Link href={route('var.index')} className="text-gray-500 hover:text-gray-700 font-medium">VAR</Link>
                        <Link href="/reglas" className="text-gray-500 hover:text-gray-700 font-medium">Reglas</Link>
                        <Link href={route('quiniela.create')} className="text-gray-500 hover:text-gray-700 font-medium">Crear quiniela</Link>
                    </>
                ) : (
                    <Link href="/reglas" className="text-gray-500 hover:text-gray-700 font-medium">Reglas</Link>
                )}
            </div>

            {/* Right */}
            <div className="flex items-center gap-3">
                {auth?.user ? (
                    <>
                        {quinielasHeader.length > 0 && (
                            <div className="hidden sm:flex flex-col items-start">
                                <span className="text-xs text-gray-400 font-medium mb-0.5">Quiniela activa</span>
                                <select
                                    value={quinielaActivaId ?? ''}
                                    onChange={handleChangeQuiniela}
                                    className="border border-gray-300 rounded-md px-3 py-1 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 min-w-[200px]"
                                >
                                    {quinielasHeader.map((q) => (
                                        <option key={q.id} value={q.id}>{q.nombre}</option>
                                    ))}
                                </select>
                            </div>
                        )}
                        <div className="hidden sm:block"><Dropdown>
                            <Dropdown.Trigger>
                                <button
                                    type="button"
                                    className="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors"
                                >
                                    {auth.user.name}
                                    <svg className="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fillRule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clipRule="evenodd" />
                                    </svg>
                                </button>
                            </Dropdown.Trigger>
                            <Dropdown.Content>
                                <Dropdown.Link href={route('profile.edit')}>Profile</Dropdown.Link>
                                <Dropdown.Link href={route('logout')} method="post" as="button">Log Out</Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown></div>

                        {/* Mobile hamburger — mismo nivel que logo */}
                        {onMenuToggle && (
                            <button
                                onClick={onMenuToggle}
                                className="sm:hidden inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none transition duration-150 ease-in-out"
                            >
                                <svg className="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    {menuOpen
                                        ? <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                                        : <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                                    }
                                </svg>
                            </button>
                        )}
                    </>
                ) : (
                    <>
                        <Link href={route('reglas.index')} className="md:hidden text-gray-700 hover:text-gray-900 font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            Reglas
                        </Link>
                        <Link href={route('login')} className="text-gray-700 hover:text-gray-900 font-semibold px-4 py-2.5 rounded-lg transition-colors">
                            Login
                        </Link>
                        <Link href={route('register')} className="bg-green-500 hover:bg-green-600 text-white font-semibold px-5 py-2.5 rounded-lg transition-colors">
                            Registrarse
                        </Link>
                    </>
                )}
            </div>
        </nav>
    );
}
