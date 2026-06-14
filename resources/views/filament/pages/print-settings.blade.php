<x-filament-panels::page>
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div style="margin-top: 2rem;">
            <x-filament::button type="submit">
                {{ __('filamentphp-resources.resources.print_settings.actions.save.label') }}
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
