<div>
    <div class="relative bg-gray-50 rounded-lg p-3">
        <div class="font-bold text-lg text-orange-700">Summary</div>
        <!-- Text container with conditional max-height -->
        <div class="{{ !$isExpanded ? 'line-clamp-3' : '' }} {{ strlen($text) > 1000 ? 'max-h-96 overflow-y-auto' : '' }}">
            {!! $text !!}
        </div>

        <!-- Expand/Collapse button -->
        <button
            wire:click="toggle"
            class="mt-2 text-orange-700 hover:text-orange-700 focus:outline-none flex items-center gap-1"
        >
            <span>{{ $isExpanded ? 'Show Less' : 'Show More' }}</span>
            <svg class="w-4 h-4 transform {{ $isExpanded ? 'rotate-180' : '' }}"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
    </div>
</div>
