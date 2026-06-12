<x-filament::page>
    @php $fechas = $this->getFechasDisponibles(); @endphp

    @if(empty($fechas))
        <x-filament::card>
            <p class="text-sm text-gray-500">No hay inserciones registradas aún.</p>
        </x-filament::card>
    @else

    <div class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Fecha:</label>
        <select wire:model="fechaSeleccionada" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            @foreach($fechas as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    @php $entradas = $this->getEntradas(); @endphp

    @if(empty($entradas))
        <x-filament::card>
            <p class="text-sm text-gray-500">Sin inserciones para esta fecha.</p>
        </x-filament::card>
    @else
        <x-filament::card>
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-mono">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-gray-500">
                            <th class="pb-2 pr-4">Hora</th>
                            <th class="pb-2 pr-4">Acción</th>
                            <th class="pb-2 pr-4">Usuario</th>
                            <th class="pb-2 pr-4">Quiniela</th>
                            <th class="pb-2 pr-4">Juego</th>
                            <th class="pb-2 pr-4">Nuevo</th>
                            <th class="pb-2 pr-4">Anterior</th>
                            <th class="pb-2">IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($entradas as $e)
                            <tr class="{{ $e['accion'] === 'INSERT' ? 'bg-green-50' : 'bg-yellow-50' }} hover:bg-opacity-80">
                                <td class="py-1.5 pr-4 text-gray-400">{{ $e['hora'] }}</td>
                                <td class="py-1.5 pr-4">
                                    <span class="rounded px-1.5 py-0.5 text-xs font-semibold
                                        {{ $e['accion'] === 'INSERT' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                        {{ $e['accion'] }}
                                    </span>
                                </td>
                                <td class="py-1.5 pr-4 text-gray-800">{{ $e['usuario'] }}</td>
                                <td class="py-1.5 pr-4 text-gray-600">#{{ $e['quinielaId'] }}</td>
                                <td class="py-1.5 pr-4 text-gray-600">#{{ $e['juegoId'] }}</td>
                                <td class="py-1.5 pr-4 font-semibold text-gray-800">{{ $e['nuevo'] }}</td>
                                <td class="py-1.5 pr-4 text-gray-400">{{ $e['anterior'] ?? '—' }}</td>
                                <td class="py-1.5 text-gray-400">{{ $e['ip'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-filament::card>
    @endif
    @endif
</x-filament::page>
