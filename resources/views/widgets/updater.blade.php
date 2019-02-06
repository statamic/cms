<div class="card p-0  content">
    <div class="p-3">
        <h1>Updates Overview</h1>
        @if ($count)
            <p>{{ $count }} updates available!</p>
        @else
            <p>Everything is up to date!</p>
        @endif
    </div>

    @if ($hasStatamicUpdate)
        <div class="p-3 border-t flex items-center">
            <div class="h-8 w-8 mr-2 text-blue">
                @svg('hammer-wrench')
            </div>
            <div class="flex-1 mr-3">
                <h3 class="mb-0">Statamic Core Update</h3>
            </div>
            <a href="{{ cp_route('updater.products.index', 'statamic') }}" class="btn btn-primary min-w-xs block">
                Update
            </a>
        </div>
    @endif

    @foreach ($updatableAddons as $slug => $name)
        <div class="p-3 border-t flex items-center">
            <div class="h-8 w-8 mr-2 text-blue">
                @svg('addons')
            </div>
            <div class="flex-1 mr-3">
                <h3 class="mb-0">{{ $name }}</h3>
            </div>
            <a href="{{ cp_route('updater.products.index', $slug) }}" class="btn btn-primary min-w-xs block">
                Update
            </a>
        </div>
    @endforeach
</div>
