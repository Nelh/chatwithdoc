<x-filament-panels::page>
    @if($this->record->type == "imported")
        @include('components.container-pdf')
    @else
        @include('components.container-document')
    @endif
</x-filament-panels::page>


