<div class="card p-0 content">
    <div class="p-3">
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}</p>
    </div>
    <div class="p-3 border-t md:flex items-center">
        <div class="h-8 w-8 mr-2 hidden md:block text-blue">
            @svg('book-pages')
        </div>
        <div class="flex-1 mb-2 md:mb-0 md:mr-3">
            <h3 class="mb-0">{{ __('Read the Documentation') }}</h3>
            <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
        </div>
        <a href="https://statamic.dev" class="btn btn-primary min-w-xs block">
            {{ __('Read the Docs') }}
        </a>
    </div>
    <div class="p-3 border-t md:flex items-center">
        <div class="h-8 w-8 mr-2 hidden md:block text-blue">
            @svg('content-writing')
        </div>
        <div class="flex-1 mb-2 md:mb-0 md:mr-3">
            <h3 class="mb-0">{{ __('Collections') }}</h3>
            <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
        </div>
        <a href="{{ cp_route('collections.create') }}" class="btn btn-primary min-w-xs block">
            {{ __('Create Collection') }}
        </a>
    </div>
    <div class="p-3 border-t md:flex items-center">
        <div class="h-8 w-8 mr-2 hidden md:block text-blue">
            @svg('blueprints')
        </div>
        <div class="flex-1 mb-2 md:mb-0 md:mr-3">
            <h3 class="mb-0">{{ __('Blueprints') }}</h3>
            <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
        </div>
        <a href="{{ cp_route('blueprints.create') }}" class="btn btn-primary min-w-xs block">
            {{ __('Create Blueprint') }}
        </a>
    </div>
    <div class="p-3 border-t md:flex items-center">
        <div class="h-8 w-8 mr-2 hidden md:block text-blue">
            @svg('structures')
        </div>
        <div class="flex-1 mb-2 md:mb-0 md:mr-3">
            <h3 class="mb-0">{{ __('Structures') }}</h3>
            <p>{{ __('statamic::messages.getting_started_widget_structures') }}</p>
        </div>
        <a href="{{ cp_route('structures.create') }}" class="btn btn-primary min-w-xs block">
            {{ __('Create Structure') }}
        </a>
    </div>
    {{-- <div class="p-3 border-t md:flex items-center">
        <div class="h-8 w-8 mr-2 hidden md:block text-blue">
            @svg('addons')
        </div>
        <div class="flex-1 mb-2 md:mb-0 md:mr-3">
            <h3 class="mb-0">__('Explore the Addon Marketplace') }}</h3>
            <p>{{ __('statamic::messages.getting_started_widget_addons') }}</p>
        </div>
        <a href="{{ cp_route('addons.index') }}" class="btn btn-primary min-w-xs block">
            {{ __('Explore Addons') }}
        </a>
    </div> --}}
</div>
