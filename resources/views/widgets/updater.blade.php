<div class="card p-0 content flex h-full">
    <div class="rounded-l py-2 md:w-40 border-r h-full flex items-center justify-center bg-grey-10">
        @if ($count)
            <div class="svg-icon flex px-1 items-center justify-center h-full" style="width: 9rem; ">
                @svg('marketing/tooter-yay')
            </div>
        @else
            <div class="svg-icon w-24 flex px-1 items-center justify-center h-full">
                @svg('marketing/tooter-nay')
            </div>
        @endif
    </div>
    <div class="p-3 flex flex-1 items-center">
        <div>
            @if ($count)
                <h1>{{ __('statamic::messages.updates_available') }}</h1>
                <p class="text-base">{{ __('There is a new version of Statamic available.') }}</p>
            @else
                <h1>{{ __('Everything is up to date.') }}</h1>
                <p class="text-base">{{ __('This site is running the newest version of Statamic.') }}</p>
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
