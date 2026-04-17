<x-filament-panels::page>
    {{-- Añade este componente de tabs antes de la tabla --}}
    <x-filament::tabs>
        @foreach ($this->getTabs() as $key => $tab)
            <x-filament::tabs.item
                :active="$activeTab === $key"
                wire:click="$set('activeTab', '{{ $key }}')"
                :badge="$tab->getBadge()"
            >
                {{ $tab->getLabel() }}
            </x-filament::tabs.item>
        @endforeach
    </x-filament::tabs>

    {{-- Tu tabla --}}
    {{ $this->table }}
</x-filament-panels::page>