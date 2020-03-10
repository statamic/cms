<div class="card p-0 content">
    <div class="py-3 px-4 border-b">
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}
            Please remember: <b>Statamic is commercial software</b> and you may need to purchase an appropriate license to continue using Statamic after the beta period has ended.
        </p>
    </div>
    <div class="flex flex-wrap p-2">
        <a href="https://statamic.dev" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('book-pages')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Read the Documentation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('collections.create') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('content-writing')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Collection') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('blueprints.create') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('blueprints')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Blueprint') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('structures.create') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @svg('structures')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Structure') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_structures') }}</p>
            </div>
        </a>
    </div>
</div>
