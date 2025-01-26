<div class="relative">
    <style>
        .page-container {
            transform-origin: top left;
            transform: scale(0.2);
            width: fit-content;
            margin-left: 20px;
        }

        .wrapper-container {
            height: 250px;
            overflow: hidden;
            margin-bottom: 10px;
        }
    </style>


    <x-filament::modal
        width="5xl"
        alignment="center"
        :close-by-clicking-away="false"
        id="template-document"
    >
        <x-slot name="trigger">
            <x-filament::button
                type="button"
                icon="heroicon-o-squares-plus"
                wire:click="openModal"
                color="gray">
                Select Template
            </x-filament::button>
        </x-slot>

        <x-slot name="heading">
            Select Template
        </x-slot>

        <x-slot name="description">
            <div class="mb-6 w-40">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedCategory">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category }}">{{ $category }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 text-start items-start justify-start">
                @foreach($templates as $template)
                    <div type="button"
                        wire:confirm="The selected template will overwite the current template. Are you sure you want to continue?"
                        wire:click="selectTemplate('{{ $template->uuid }}'); $dispatch('close-modal', { id: 'template-document' })" style="width: 200px"
                        class="cursor-pointer group relative hover:ring-2 hover:ring-primary-500 rounded-lg transition-all duration-200 "
                        {{-- {{ $currentTemplateTitle === $template->title ? 'ring-2 ring-primary-500' : '' }} --}}
                    >
                        <p class="text-xs text-center font-medium text-gray-900 my-2">{{ $template->name }}</p>
                        <div class="wrapper-container">
                            <div class="page-container">
                                <div class="prose page {{ $template->title }}">
                                    {!!$template->content !!}
                                </div>
                            </div>
                            <span class="absolute bottom-2 right-5 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                                {{ $template->category }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-slot>
    </x-filament::modal>
</div>
