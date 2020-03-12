<div class="card p-0 content">
    <div class="bg-blue-500 rounded-t px-3 py-2">
        <p class="font-bold text-white text-lg">Welcome to the Statamic 3 Beta!</p>
        <p class="text-white text-sm"><b>Statamic is commercial software:</b> you may need to purchase an appropriate license to continue using Statamic after the beta.</p>
    </div>
    <div class="p-3">
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}</p>
    </div>
    <div class="flex flex-wrap">
        <a href="https://statamic.dev" class="w-full lg:w-1/2 p-3 border-t lg:border-r md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('book-pages')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Read the Documentation') }} &rarr;</h3>
                <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('collections.create') }}" class="w-full lg:w-1/2 p-3 border-t md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('content-writing')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Create a Collection') }} &rarr;</h3>
                <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('blueprints.create') }}" class="w-full lg:w-1/2 p-3 border-t lg:border-r md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('blueprints')
            </div>
            <div class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Create a Blueprint') }} &rarr;</h3>
                <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
            </div>
        </a>
        <div class="w-full lg:w-1/2 p-3 border-t lg:border-r md:flex items-start hover:bg-grey-10 group">
            <div class="h-8 w-8 mr-2 hidden md:block text-blue">
                @svg('structures')
            </div>
            <a href="{{ cp_route('navigation.create') }}" class="flex-1 mb-2 md:mb-0 md:mr-3">
                <h3 class="mb-1 text-blue group-hover:text-grey-80">{{ __('Create a Navigation') }} &rarr;</h3>
                <p>{{ __('statamic::messages.getting_started_widget_navigation') }}</p>
            </a>
        </div>
    </div>
</div>
