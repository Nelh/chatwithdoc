<div class="ml-4 flex flex-col gap-3 justify-between" style="height: calc(100vh - 100px); margin-left: 5px">
    <livewire:expandable-text text="{{ $this->document->context }}" />

    <!-- Chat Messages -->
    @if(count($chats) > 0)
        <div class="flex flex-col-reverse gap-3 p-3 flex-1 overflow-y-auto bg-gray-50 rounded-lg
            [&::-webkit-scrollbar]:w-2
            [&::-webkit-scrollbar-track]:rounded-full
            [&::-webkit-scrollbar-track]:bg-transparent
            [&::-webkit-scrollbar-thumb]:rounded-full
            [&::-webkit-scrollbar-thumb]:bg-gray-200"  id="chat-container">
            {{-- {{ var_dump($chats)}} --}}
            @foreach($chats as $chat)
                <!-- Answer -->
                <div class="flex justify-start mt-2">
                    <div class="bg-gray-200 rounded-lg py-2 px-4 max-w-[80%]">
                        {{ $chat->answer }}
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $chat->created_at->format('g:i A') }}
                        </div>
                    </div>
                </div>
                <!-- Question -->
                <div class="flex justify-start mt-2">
                    <div class="bg-gray-700 text-white rounded-lg py-2 px-4 max-w-[80%]">
                        {{ $chat->question }}
                        <div class="text-xs text-gray-200 mt-1">
                            {{ $chat->created_at->format('g:i A') }}
                        </div>
                    </div>
                </div>
            @endforeach

            @if($streamingResponse)
                <!-- Streaming Answer -->
                <div class="flex justify-start mt-2">
                    <div class="bg-gray-200 rounded-lg py-2 px-4 max-w-[80%]">
                        <span wire:stream="currentAnswer"></span>
                        <span class="animate-pulse">▊</span>
                    </div>
                </div>
                <!-- Streaming Question -->
                <div class="flex justify-start mt-2">
                    <div class="bg-gray-700 text-white rounded-lg py-2 px-4 max-w-[80%]">
                        {{ $question }}
                    </div>
                </div>
            @endif

            @if($loading && !$streamingResponse)
                <div class="flex justify-center">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-gray-900"></div>
                </div>
            @endif
        </div>
    @else
    <div class="flex flex-col flex-1 items-center justify-center p-3 bg-gray-50 rounded-lg w-full h-full m-auto">
        <svg width="50" height="50" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round"></g><g id="SVGRepo_iconCarrier"> <path d="M4 12C4 7.58172 7.58172 4 12 4V4C16.4183 4 20 7.58172 20 12V17.0909C20 17.9375 20 18.3608 19.8739 18.6989C19.6712 19.2425 19.2425 19.6712 18.6989 19.8739C18.3608 20 17.9375 20 17.0909 20H12C7.58172 20 4 16.4183 4 12V12Z" stroke="#222222"></path> <path d="M9 11L15 11" stroke="#222222" stroke-linecap="round" stroke-linejoin="round"></path> <path d="M12 15H15" stroke="#222222" stroke-linecap="round" stroke-linejoin="round"></path> </g></svg>
        <div>Chat With AI</div>
    </div>
    @endif

    <div class="relative">
        <livewire:auto-suggestion wire:key="{{ $this->suggestion_key }}" :chats="true" />

        <form wire:submit.prevent="askQuestion">
            <input
                class="pb-16 p-4 block w-full border-gray-200 rounded-lg text-sm focus:border-gray-500 focus:ring-gray-500 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600"
                wire:model.debounce.300ms="question"
                placeholder="Ask a question about the document..."
                wire:loading.attr="disabled" />

            <div class="absolute bottom-px inset-x-px p-2 rounded-b-md bg-white dark:bg-neutral-900">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <div class="text-sm px-2 text-light text-gray-400">
                            Token remaining: {{ auth()?->user()?->available_tokens ?? 0 }}
                        </div>
                    </div>
                    <div class="flex items-center gap-x-1">
                        <div class="inline-flex shrink-0 justify-center items-center size-8 rounded-lg text-gray-500 hover:bg-gray-100 focus:z-10 focus:outline-none focus:bg-gray-100 dark:text-neutral-500 dark:hover:bg-neutral-800 dark:focus:bg-neutral-800">
                        </div>
                        <x-filament::button
                            type="submit"
                            icon="heroicon-o-sparkles"
                            class="disabled:opacity-50"
                            wire:loading.attr="disabled"
                            wire:disabled="!question">
                            <span wire:loading.remove>Submit</span>
                            <span wire:loading>Processing...</span>
                        </x-filament::button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

@script
<script>
    // Auto-scroll to bottom
    const chatContainer = document.getElementById('chat-container');

    const scrollToBottom = () => {
        chatContainer.scrollTop = chatContainer.scrollHeight;
    };

    // Scroll on new messages
    $wire.on('updateAnswer', () => {
        scrollToBottom();
    });

    // Initial scroll
    scrollToBottom();
</script>
@endscript
