<div class="card p-0 content">
    <div class="bg-pink rounded-t px-3 py-2">
        <p class="font-bold text-white text-lg">Welcome to the Statamic 3 Beta!</p>
        <p class="text-white text-sm">Remember: Statamic is paid, commercial software. After the beta you may need to purchase an appropriate license to continue using Statamic.</p>
    </div>
    <div class="p-3">
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}</p>
    </div>
    <div class="flex flex-wrap">
        <div class="w-full lg:w-1/2 p-3 border-t md:flex items-start">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('book-pages')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-0">{{ __('Read the Documentation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
                <a href="https://statamic.dev" class="text-blue text-sm font-bold">
                    {{ __('Read the Docs') }} &rarr;
                </a>
            </div>
        </div>
        <div class="w-full lg:w-1/2 p-3 border-t md:flex items-start">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('content-writing')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-0">{{ __('Collections') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
                <a href="{{ cp_route('collections.create') }}" class="text-blue text-sm font-bold">
                    {{ __('Create Collection') }} &rarr;
                </a>
            </div>
        </div>
        <div class="w-full lg:w-1/2 p-3 border-t md:flex items-start">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('blueprints')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-0">{{ __('Blueprints') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
                <a href="{{ cp_route('blueprints.create') }}" class="text-blue text-sm font-bold">
                    {{ __('Create Blueprint') }} &rarr;
                </a>
            </div>
        </div>
        <div class="w-full lg:w-1/2 p-3 border-t md:flex items-start">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('structures')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-0">{{ __('Structures') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_structures') }}</p>
                <a href="{{ cp_route('structures.create') }}" class="text-blue text-sm font-bold">
                    {{ __('Create Structure') }} &rarr;
                </a>
            </div>
        </div>
        {{-- <div class="p-3 border-t md:flex items-center">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('addons')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-0">__('Explore the Addon Marketplace') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_addons') }}</p>
                <a href="{{ cp_route('addons.index') }}" class="btn btn-primary min-w-xs block">
             __('Explore Addons') }}
            </a>
            </div>
        </div> --}}
    </div>
</div>
