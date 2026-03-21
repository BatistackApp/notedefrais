<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-center p-4">
            <x-filament::button
                href="{{ $this->getCreateUrl() }}"
                tag="a"
                size="xl"
                icon="heroicon-m-camera"
                class="w-full max-w-md py-6 text-xl shadow-lg"
            >
                Nouvelle dépense (Photo)
            </x-filament::button>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
