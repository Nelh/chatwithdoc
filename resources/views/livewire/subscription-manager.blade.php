<div class="w-full mt-8">
    <!-- Current Subscription -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
        <div class="px-4 py-5 sm:p-6">
            <div class="mb-8">
                <img src="{{ asset('images/logo.png')}}" class="w-16" />
                <p class="my-2">Powered By <a href="https://aivent.co/" target="_blank" class="font-bold">Aivent</a></p>
            </div>
            <h2 class="text-lg font-medium text-gray-900 mb-4">Current Subscription</h2>

            @if($currentSubscription)
                <div class="bg-gray-50 rounded-md p-4 flex justify-between">
                    <div>
                        @if($plan)
                            <p>{{ 'Curren plan: ' . $plan->name }}</p>
                            <p>{{ $plan->description }}</p>
                            <p>{{ $plan->price . '/' . $plan->interval }}</p>
                        @endif
                        <!-- Subscription Status -->
                        <div class="mb-4">
                            <div class="flex items-center">
                                <span class="mr-2">Status:</span>
                                @if($currentSubscription['cancelled'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Cancelled
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @endif
                            </div>

                            @if($currentSubscription['on_trial'])
                                <p class="text-sm text-gray-500 mt-1">
                                    Trial ends on {{ $currentSubscription['trial_ends_at'] }}
                                </p>
                            @endif

                            @if($currentSubscription['cancelled'] && $currentSubscription['on_grace_period'])
                                <p class="text-sm text-gray-500 mt-1">
                                    Access until {{ $currentSubscription['ends_at'] }}
                                </p>
                            @endif
                        </div>

                        <!-- Payment Method -->
                        @if($currentSubscription['payment_method']['last_four'])
                            <div class="mb-4">
                                <p class="text-sm text-gray-600">
                                    Payment Method: {{ ucfirst($currentSubscription['payment_method']['type']) }}
                                    ending in {{ $currentSubscription['payment_method']['last_four'] }}
                                </p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="mt-4 space-x-3">
                            @if(!$currentSubscription['cancelled'])
                                <button
                                    wire:click="cancelSubscription"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    Cancel Subscription
                                </button>
                            @elseif($currentSubscription['on_grace_period'])
                                <button
                                    wire:click="resumeSubscription"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Resume Subscription
                                </button>
                            @endif

                            <button
                                wire:click="openBillingPortal"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-neutral-500"
                            >
                                Manage Billing
                            </button>
                        </div>
                    </div>
                    <div>
                        <hr>
                        <div class="text-right text-gray-800 mt-4">
                            <div>Aivalable Token: {{ $this->user?->available_tokens ?? 0 }}</div>
                            <div>Aivalable Storage: {{ $this->user?->storage_limit ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            @else
                <p class="text-gray-500">No active subscription</p>
            @endif
        </div>
    </div>

    <!-- Available Plans -->
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-lg font-medium text-gray-900">Available Plans</h2>

                <!-- Interval Toggle Switch -->
                <div class="flex items-center gap-3 bg-gray-100 rounded-lg p-1">
                    <button
                        wire:click="toggleInterval"
                        @class([
                            'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            'bg-white shadow text-gray-900' => $interval === 'month',
                            'text-gray-500 hover:text-gray-900' => $interval === 'year'
                        ])
                    >
                        Monthly
                    </button>
                    <button
                        wire:click="toggleInterval"
                        @class([
                            'px-4 py-2 text-sm font-medium rounded-md transition-colors',
                            'bg-white shadow text-gray-900' => $interval === 'year',
                            'text-gray-500 hover:text-gray-900' => $interval === 'month'
                        ])
                    >
                        Yearly
                        @if($interval === 'year')
                            <span class="ml-1 text-xs text-green-500">Save up to 17%</span>
                        @endif
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($this->filteredPlans as $plan)
                    <div class="relative rounded-lg border border-gray-300 bg-white px-6 py-5 shadow-sm flex flex-col">
                        <div class="flex-1">
                            <h3 class="text-lg font-medium text-gray-900">{{ $plan->name }}</h3>
                            <p class="mt-2 text-2xl font-bold text-gray-900">
                                ${{ number_format($plan->price, 2) }}
                                <span class="text-sm font-normal text-gray-500">/{{ $plan->interval }}</span>
                            </p>
                            <p class="mt-4 text-gray-500">{{ $plan->description }}</p>
                        </div>

                        <div class="mt-6">
                            <button
                                wire:loading.attr="disabled"
                                wire:click="subscribe('{{ $plan->stripe_price_id }}')"
                                @class([
                                    'w-full inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm disabled:pointer-events-none disabled:bg-neutral-600/80',
                                    'text-white bg-neutral-600 hover:bg-neutral-700' => !($currentSubscription && $currentSubscription['price_id'] === $plan->stripe_price_id),
                                    'text-neutral-700 bg-neutral-100' => $currentSubscription && $currentSubscription['price_id'] === $plan->stripe_price_id
                                ])
                            >
                                <span wire:loading.remove wire:target="subscribe">
                                    @if($currentSubscription && $currentSubscription['price_id'] === $plan->stripe_price_id)
                                        Current Plan
                                    @else
                                        {{ $currentSubscription ? 'Switch Plan' : 'Subscribe' }}
                                    @endif
                                </span>
                                <span wire:loading wire:target="subscribe">
                                    Processing...
                                </span>
                            </button>
                        </div>

                        @if($currentSubscription && $currentSubscription['price_id'] === $plan->stripe_price_id)
                            <div class="mt-4">
                                @if($currentSubscription['cancelled'])
                                    <button
                                        wire:click="resumeSubscription"
                                        class="w-full text-sm text-gray-600 hover:text-gray-900"
                                    >
                                        Resume Subscription
                                    </button>
                                @else
                                    <button
                                        wire:click="cancelSubscription"
                                        class="w-full text-sm text-red-600 hover:text-red-900"
                                    >
                                        Cancel Subscription
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            @if($currentSubscription)
                <div class="mt-6 text-center">
                    <button
                        wire:click="openBillingPortal"
                        class="text-sm text-gray-600 hover:text-gray-900"
                    >
                        Manage Billing
                    </button>
                </div>
            @endif
        </div>
    </div>
    <!-- Flash Messages -->
    <div
        x-data="{ show: false, message: '' }"
        x-on:subscription-updated.window="show = true; message = $event.detail.message; setTimeout(() => show = false, 3000)"
        x-show="show"
        x-transition
        class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg"
        style="display: none;"
    >
        <p x-text="message"></p>
    </div>
</div>
