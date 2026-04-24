<x-filament::page>
    <div class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium text-gray-700">Fecha:</label>
        <select wire:model="fechaSeleccionada" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm">
            @foreach($this->getFechasDisponibles() as $valor => $etiqueta)
                <option value="{{ $valor }}">{{ $etiqueta }}</option>
            @endforeach
        </select>
    </div>

    @php $entradas = $this->getEntradas(); @endphp

    @if(empty($entradas))
        <x-filament::card>
            <p class="text-sm text-gray-500">Sin logs para esta fecha.</p>
        </x-filament::card>
    @else
        <x-filament::card>
            <div class="space-y-1 font-mono text-xs">
                @foreach($entradas as $e)
                    @php
                        $bg = match($e['tipo']) {
                            'inicio'     => 'bg-blue-50 border-blue-200',
                            'completada' => 'bg-green-50 border-green-200',
                            'cambio'     => 'bg-yellow-50 border-yellow-200',
                            'activo'     => 'bg-purple-50 border-purple-200',
                            'espera'     => 'bg-gray-50 border-gray-200',
                            default      => 'bg-white border-gray-100',
                        };
                        $dot = match($e['tipo']) {
                            'inicio'     => 'bg-blue-400',
                            'completada' => 'bg-green-400',
                            'cambio'     => 'bg-yellow-400',
                            'activo'     => 'bg-purple-400',
                            'espera'     => 'bg-gray-400',
                            default      => 'bg-gray-300',
                        };
                    @endphp
                    <div class="flex items-start gap-2 rounded border px-3 py-2 {{ $bg }}">
                        <span class="mt-1.5 h-2 w-2 flex-shrink-0 rounded-full {{ $dot }}"></span>
                        <span class="w-16 flex-shrink-0 text-gray-400">{{ $e['hora'] }}</span>
                        <span class="flex-1 text-gray-800">
                            {{ $e['mensaje'] }}
                            @if($e['json'])
                                <span class="ml-2 text-gray-500">
                                    @foreach($e['json'] as $k => $v)
                                        <span class="mr-2"><span class="font-semibold">{{ $k }}:</span> {{ $v }}</span>
                                    @endforeach
                                </span>
                            @endif
                        </span>
                    </div>
                @endforeach
            </div>
        </x-filament::card>
    @endif
</x-filament::page>
