<div class="mt-2 p-4 bg-neutral-100 border-l-4 border-neutral-500">
    <div class="space-y-3">
        @foreach($inputs as $index => $input)
            <div class="flex items-start gap-2">
                <div class="grid grid-cols-4 gap-4">
                    <div class="flex items-center mb-4">
                        <span class="text-sm font-medium text-neutral-900 ml-2 block">
                            {{ $index === 0 ? "Main signer" : "Additional signer" }}
                        </span>
                    </div>

                    <input type="hidden"
                           wire:model="inputs.{{ $index }}.signing_method"
                           value="{{ $index === 0 ? 'embedded' : 'remote' }}"
                    >

                    <!-- Signature Code Select -->
                    <x-filament::input.wrapper
                        :valid="!empty($input['code'])"
                        :invalid="empty($input['code'])">
                        <x-filament::input.select
                            wire:model.live="inputs.{{ $index }}.code"
                            :disabled="$index === 0"
                            placeholder="Select signature code">
                            @foreach($this->getAvailableCodesForSelect($index) as $code)
                                <option value="{{ $code }}" @selected($input['code'] === $code)>
                                    {{ $code }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                        @error("inputs.{$index}.code")
                            <p class="text-sm text-neutral-600">{{ $message }}</p>
                        @enderror
                    </x-filament::input.wrapper>

                    <!-- Name Input -->
                    <x-filament::input.wrapper
                        prefix-icon="heroicon-m-user"
                        prefix-icon-color="gray"
                        :valid="!empty($input['name'])"
                        :invalid="empty($input['name'])">
                        <x-filament::input
                            type="text"
                            wire:model.live="inputs.{{ $index }}.name"
                            placeholder="Enter name"
                        />
                        @error("inputs.{$index}.name")
                            <p class="text-sm text-neutral-600">{{ $message }}</p>
                        @enderror
                    </x-filament::input.wrapper>

                    <!-- Email Input -->
                    <x-filament::input.wrapper
                        prefix-icon="heroicon-m-envelope"
                        prefix-icon-color="gray"
                        :valid="!empty($input['email']) && filter_var($input['email'], FILTER_VALIDATE_EMAIL)"
                        :invalid="empty($input['email']) || !filter_var($input['email'], FILTER_VALIDATE_EMAIL)">
                        <x-filament::input
                            type="email"
                            wire:model.live="inputs.{{ $index }}.email"
                            placeholder="Enter email"
                        />
                        @error("inputs.{$index}.email")
                            <p class="text-sm text-neutral-600">{{ $message }}</p>
                        @enderror
                    </x-filament::input.wrapper>
                </div>

                @if(count($inputs) > 1 && $index !== 0)
                    <button type="button"
                            wire:click="removeInput({{ $index }})"
                            class="text-neutral-600 hover:text-neutral-700 mt-2">
                        <x-heroicon-o-minus-circle class="h-5 w-5" />
                    </button>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-3">
        <x-filament::button
            wire:click="addInput"
            type="button">
            Add Signer
        </x-filament::button>
    </div>
</div>
