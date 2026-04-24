<x-filament::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">

        <x-filament::card>
            <h2 class="text-lg font-bold mb-2">Sincronizar API</h2>
            <p class="text-sm text-gray-500 mb-4">Llama la API de football-data.org y recalcula puntos de todos los partidos activos.</p>
            <x-filament::button wire:click="syncApi" wire:loading.attr="disabled" color="primary">
                <span wire:loading.remove wire:target="syncApi">Sincronizar ahora</span>
                <span wire:loading wire:target="syncApi">Sincronizando...</span>
            </x-filament::button>
        </x-filament::card>

        <x-filament::card>
            <h2 class="text-lg font-bold mb-2">Recalcular Inválidos</h2>
            <p class="text-sm text-gray-500 mb-4">Recalcula predicciones marcadas como INVALID que sí tienen marcador ingresado (ej: quinielas 0-0).</p>
            <x-filament::button wire:click="recalcularInvalidos" wire:loading.attr="disabled" color="warning">
                <span wire:loading.remove wire:target="recalcularInvalidos">Recalcular inválidos</span>
                <span wire:loading wire:target="recalcularInvalidos">Procesando...</span>
            </x-filament::button>
        </x-filament::card>

        <x-filament::card>
            <h2 class="text-lg font-bold mb-2">Recalcular Puntajes</h2>
            <p class="text-sm text-gray-500 mb-4">Recalcula predicciones de partidos FINISHED que aún no tienen status final correcto.</p>
            <x-filament::button wire:click="recalcularPuntajes" wire:loading.attr="disabled" color="success">
                <span wire:loading.remove wire:target="recalcularPuntajes">Recalcular puntajes</span>
                <span wire:loading wire:target="recalcularPuntajes">Procesando...</span>
            </x-filament::button>
        </x-filament::card>

    </div>
</x-filament::page>
