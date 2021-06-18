<div class="card p-0 content">
    <div class="py-3 px-4 border-b">
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}</p>
    </div>
    <div class="flex flex-wrap p-2">
        <a href="https://statamic.dev" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('book-pages')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Read the Documentation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
            </div>
        </a>
        @if (!Statamic::pro())
        <a href="https://statamic.dev/licensing" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('pro-ribbon')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Enable Pro Mode') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_pro') }}</p>
            </div>
        </a>
        @endif
        <a href="{{ cp_route('collections.create') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('content-writing')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Collection') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('blueprints.index') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('blueprints')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Blueprint') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('navigation.create') }}" class="w-full lg:w-1/2 p-2 flex items-start hover:bg-grey-20 rounded-md group">
            <div class="h-8 w-8 mr-2 text-grey-80">
                @cp_svg('hierarchy-files')
            </div>
            <div class="flex-1">
                <h3 class="mb-1 text-blue">{{ __('Create a Navigation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_navigation') }}</p>
            </div>
        </a>
    </div>
</div>
