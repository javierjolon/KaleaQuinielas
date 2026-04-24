<x-filament::page>
    <x-filament::card>
        <form wire:submit.prevent="guardar">
            {{ $this->form }}
            <div class="mt-4">
                <x-filament::button type="submit">
                    Guardar configuración
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament::page>
