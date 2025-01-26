<div>
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($suggestions as $suggestion)
            <button
                type="button"
                class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-full bg-neutral-200 text-neutral-700 hover:bg-neutral-200 transition-colors duration-200"
                wire:key="suggestion-{{ $suggestion_key }}-{{ $loop->index }}"
                @if($chats)
                    wire:click="selectSuggestion('{{ addslashes($suggestion) }}')"
                @else
                    @click="inputText = '{{ addslashes($suggestion) }}'"
                @endif
            >
                {{ $suggestion }}
            </button>
        @endforeach
    </div>
    <button
        type="button"
        class="my-2 text-sm text-orange-700 hover:text-orange-900 "
        wire:click="refreshSuggestions"
    >
        <div class="flex items-center gap-1">
            <x-heroicon-o-arrow-path class="w-4 h-4" />
            Refresh suggestions
        </div>
    </button>
</div>
