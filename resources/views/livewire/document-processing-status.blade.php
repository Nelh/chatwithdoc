<div>
    <div class="flex items-center">
        @if($status === 'processing')
            <x-filament::loading-indicator class="h-4 w-4 mr-2 text-primary-500" />
            <span class="text-gray-500">Processing...</span>
        @elseif($status === 'completed')
            <x-heroicon-o-check-circle class="h-4 w-4 mr-2 text-success-500" />
            <span class="text-success-500">Completed</span>
        @elseif($status === 'failed')
            <x-heroicon-o-x-circle class="h-4 w-4 mr-2 text-danger-500" />
            <span class="text-danger-500">Failed</span>
        @endif
    </div>
</div>
