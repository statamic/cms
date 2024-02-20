<div class="card p-0 h-full">
    <header class="flex justify-between items-center p-4 border-b">
        <h2 class="flex items-center">
            <div class="h-6 w-6 mr-2 text-gray-800">
                @cp_svg('icons/light/loading-bar')
            </div>
            <span>{{ __('Updates') }}</span>
        </h2>
        @if ($count)
            <a href="{{ cp_route('updater') }}" class="badge-sm bg-green-600 text-white">
                {{ trans_choice('1 update available|:count updates available', $count) }}
            </a>
        @endif
    </header>
    <section class="px-4 py-2">
        @if (! $count)
            <p class="text-base text-center text-gray-700">{{ __('Everything is up to date.') }}</p>
        @endif

        @if ($hasStatamicUpdate)
            <div class="flex items-center justify-between text-sm">
                <a href="{{ cp_route('updater.product', 'statamic') }}"class="hover:text-blue font-bold py-1">Statamic Core</a>
            </div>
        @endif

        @foreach ($updatableAddons as $slug => $addon)
            <div class="flex items-center justify-between w-full text-sm">
                <a href="{{ cp_route('updater.product', $slug) }}" class="hover:text-blue py-1">{{ $addon }}</a>
            </div>
        @endforeach
    </section>
</div>
