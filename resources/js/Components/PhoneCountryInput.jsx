import { useState, useRef, useEffect } from 'react';

export default function PhoneCountryInput({ countries = [], telefono, pais, onTelefonoChange, onPaisChange, error, disabled = false }) {
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState('');
    const dropdownRef = useRef(null);

    const selected = countries.find(c => c.code === pais) ?? countries[0] ?? { dial: '' };

    const filtered = search
        ? countries.filter(c =>
            c.name.toLowerCase().includes(search.toLowerCase()) ||
            c.dial.includes(search)
          )
        : countries;

    useEffect(() => {
        function handleClick(e) {
            if (dropdownRef.current && !dropdownRef.current.contains(e.target)) {
                setOpen(false);
                setSearch('');
            }
        }
        document.addEventListener('mousedown', handleClick);
        return () => document.removeEventListener('mousedown', handleClick);
    }, []);

    function select(country) {
        onPaisChange(country.code, country.dial);
        setOpen(false);
        setSearch('');
    }

    return (
        <div className="flex gap-2 mt-1">
            <div className="relative" ref={dropdownRef}>
                <button
                    type="button"
                    disabled={disabled}
                    onClick={() => setOpen(o => !o)}
                    className="flex items-center gap-1 px-3 h-full border border-gray-300 rounded-md shadow-sm bg-white text-sm text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 disabled:opacity-50 whitespace-nowrap"
                    style={{ minWidth: '90px' }}
                >
                    <span>{selected.dial}</span>
                    <svg className="w-3 h-3 text-gray-400 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                {open && (
                    <div className="absolute z-50 mt-1 w-64 bg-white border border-gray-200 rounded-md shadow-lg">
                        <div className="p-2 border-b border-gray-100">
                            <input
                                type="text"
                                autoFocus
                                placeholder="Buscar país..."
                                value={search}
                                onChange={e => setSearch(e.target.value)}
                                className="w-full text-sm border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                            />
                        </div>
                        <ul className="max-h-48 overflow-y-auto">
                            {filtered.map(country => (
                                <li
                                    key={country.code}
                                    onClick={() => select(country)}
                                    className={`flex items-center justify-between px-3 py-2 text-sm cursor-pointer hover:bg-indigo-50 ${
                                        country.code === selected.code ? 'bg-indigo-50 font-medium' : ''
                                    }`}
                                >
                                    <span>{country.name}</span>
                                    <span className="text-gray-500 ml-2">{country.dial}</span>
                                </li>
                            ))}
                            {filtered.length === 0 && (
                                <li className="px-3 py-2 text-sm text-gray-400">Sin resultados</li>
                            )}
                        </ul>
                    </div>
                )}
            </div>

            <input
                id="telefono"
                type="tel"
                inputMode="numeric"
                name="telefono"
                value={telefono}
                disabled={disabled}
                onKeyDown={e => {
                    const allowed = ['Backspace', 'Delete', 'Tab', 'Enter', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                    if (!allowed.includes(e.key) && !/^\d$/.test(e.key)) e.preventDefault();
                }}
                onChange={e => onTelefonoChange(e.target.value.replace(/\D/g, ''))}
                onPaste={e => {
                    e.preventDefault();
                    const digits = e.clipboardData.getData('text').replace(/\D/g, '');
                    onTelefonoChange(telefono + digits);
                }}
                placeholder="12345678"
                className={`flex-1 border border-gray-300 rounded-md shadow-sm text-sm px-3 focus:ring-indigo-500 focus:border-indigo-500 ${
                    error ? 'border-red-500' : ''
                }`}
                required
            />
        </div>
    );
}
