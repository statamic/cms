<div class="card p-0 h-full">
    <header class="flex justify-between items-center p-4___REPLACED border-b">
        <h2 class="flex items-center">
            <div class="h-6 w-6 mr-2___REPLACED text-grey-80">
                @cp_svg('loading-bar')
            </div>
            <span>{{ __('Updates') }}</span>
        </h2>
        @if ($count)
            <a href="{{ cp_route('updater') }}" class="badge-sm bg-green text-white">
                {{ trans_choice('1 update available|:count updates available', $count) }}
            </a>
        @endif
    </header>
    <section class="px-4___REPLACED py-1">
        @if (! $count)
            <p class="text-base text-center text-grey-70">{{ __('Everything is up to date.') }}</p>
        @endif

        @if ($hasStatamicUpdate)
            <div class="flex items-center justify-between text-sm">
                <a href="{{ cp_route('updater.product', 'statamic') }}"class="hover:text-blue font-bold py-1___REPLACED">Statamic Core</a>
            </div>
        @endif

        @foreach ($updatableAddons as $slug => $addon)
            <div class="flex items-center justify-between w-full text-sm">
                <a href="{{ cp_route('updater.product', $slug) }}" class="hover:text-blue py-1___REPLACED">{{ $addon }}</a>
            </div>
        @endforeach
    </section>
</div>
