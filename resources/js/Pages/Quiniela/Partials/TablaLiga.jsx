import { useState, useEffect } from 'react';
import axios from 'axios';

function FormBadge({ form }) {
    if (!form) return null;
    return (
        <div className="flex gap-0.5">
            {form.split('').map((c, i) => {
                const cls = c === 'W' ? 'bg-green-500' : c === 'L' ? 'bg-red-500' : 'bg-gray-400';
                return <span key={i} className={`w-2 h-2 rounded-full ${cls}`} title={c} />;
            })}
        </div>
    );
}

function GrupoTabla({ filas }) {
    const grupoLabel = filas[0]?.grupo;
    return (
        <div className="mb-6">
            {grupoLabel && (
                <div className="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1 px-1">{grupoLabel}</div>
            )}
            <div className="overflow-x-auto rounded-xl bg-white shadow-sm">
                <table className="w-full text-xs">
                    <thead>
                        <tr className="bg-azul text-white">
                            <th className="py-2 px-1 text-center w-6">#</th>
                            <th className="py-2 px-2 text-left">Equipo</th>
                            <th className="py-2 px-1 text-center">PJ</th>
                            <th className="py-2 px-1 text-center">G</th>
                            <th className="py-2 px-1 text-center">E</th>
                            <th className="py-2 px-1 text-center">P</th>
                            <th className="py-2 px-1 text-center">DG</th>
                            <th className="py-2 px-1 text-center font-bold">Pts</th>
                            <th className="py-2 px-1 text-center hidden sm:table-cell">Forma</th>
                        </tr>
                    </thead>
                    <tbody>
                        {filas.map((row, i) => (
                            <tr key={row.rank} className={i % 2 === 0 ? 'bg-white' : 'bg-gray-50'}>
                                <td className="py-2 px-1 text-center text-gray-500">{row.rank}</td>
                                <td className="py-2 px-2">
                                    <div className="flex items-center gap-1.5">
                                        <img src={row.team.logo} alt="" className="w-5 h-5 object-contain shrink-0" />
                                        <span className="truncate max-w-[90px] sm:max-w-none">{row.team.name}</span>
                                    </div>
                                </td>
                                <td className="py-2 px-1 text-center">{row.played}</td>
                                <td className="py-2 px-1 text-center">{row.win}</td>
                                <td className="py-2 px-1 text-center">{row.draw}</td>
                                <td className="py-2 px-1 text-center">{row.lose}</td>
                                <td className="py-2 px-1 text-center">{row.gd > 0 ? `+${row.gd}` : row.gd}</td>
                                <td className="py-2 px-1 text-center font-bold">{row.points}</td>
                                <td className="py-2 px-1 text-center hidden sm:table-cell">
                                    <div className="flex justify-center"><FormBadge form={row.form} /></div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </div>
    );
}

export default function TablaLiga() {
    const [data, setData] = useState(null);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(false);

    useEffect(() => {
        axios.get('/tabla-liga')
            .then(r => setData(r.data))
            .catch(() => setError(true))
            .finally(() => setLoading(false));
    }, []);

    if (loading) {
        return <div className="text-center py-10 text-gray-400 text-sm">Cargando tabla...</div>;
    }

    if (error || !data?.grupos?.length) {
        return <div className="text-center py-10 text-gray-400 text-sm">Sin datos disponibles</div>;
    }

    return (
        <div className="mt-4">
            {data.grupos.map((filas, i) => (
                <GrupoTabla key={i} filas={filas} />
            ))}
        </div>
    );
}
