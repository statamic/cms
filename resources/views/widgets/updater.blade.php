<div class="card p-0 content">
    <div class="p-3 flex items-center">
        <div class="h-24 w-24">
            @svg($count ? 'marketing/exclamation': 'marketing/shield-check')
        </div>
        <div class="ml-2">
            <h1 class="mb-sm">{{ __('Updates') }}</h1>
            @if ($count)
                <p>{{ trans_choice('statamic::messages.updates_available', $count) }}</p>
            @else
                <p>{{ __('Everything is up to date') }}</p>
            @endif
        </div>
    </div>

    @if ($hasStatamicUpdate)
        <div class="px-3 py-1 border-t flex items-center">
            <div class="h-4 w-4 mr-1 text-blue">
                @svg('hammer-wrench')
            </div>
            <div class="flex-1 mr-3">
                <a href="{{ cp_route('updater.product', 'statamic') }}"class="text-blue text-sm font-bold">Statamic Core</a>
            </div>
        </div>
    @endif

    @foreach ($updatableAddons as $slug => $name)
        <div class="px-3 py-1 border-t flex items-center">
            <div class="h-4 w-4 mr-1 text-blue">
                @svg('addons')
            </div>
            <div class="flex-1 mr-3">
                <a href="{{ cp_route('updater.product', $slug) }}" class="text-blue text-sm font-bold">{{ $name }}</a>
            </div>
        </div>
    @endforeach
</div>
