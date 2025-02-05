@php
    use function Statamic\trans as __;
@endphp

<div class="card card-lg content p-0">
    <header>
        <h1>{{ __('statamic::messages.getting_started_widget_header') }}</h1>
        <p>{{ __('statamic::messages.getting_started_widget_intro') }}</p>
    </header>
    <div class="grid p-4 lg:grid-cols-2">
        <a href="https://statamic.dev" class="super-btn">
            @cp_svg('icons/light/book-pages')
            <div class="flex-1">
                <h3>{{ __('Read the Documentation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_docs') }}</p>
            </div>
        </a>
        @if (! Statamic::pro())
            <a href="https://statamic.dev/licensing" class="super-btn">
                @cp_svg('icons/light/pro-ribbon')
                <div class="flex-1">
                    <h3>{{ __('Enable Pro Mode') }}</h3>
                    <p>{{ __('statamic::messages.getting_started_widget_pro') }}</p>
                </div>
            </a>
        @endif

        <a href="{{ cp_route('collections.create') }}" class="super-btn">
            @cp_svg('icons/light/content-writing')
            <div class="flex-1">
                <h3>{{ __('Create a Collection') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_collections') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('blueprints.index') }}" class="super-btn">
            @cp_svg('icons/light/blueprints')
            <div class="flex-1">
                <h3>{{ __('Create a Blueprint') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_blueprints') }}</p>
            </div>
        </a>
        <a href="{{ cp_route('navigation.create') }}" class="super-btn">
            @cp_svg('icons/light/hierarchy-files')
            <div class="flex-1">
                <h3>{{ __('Create a Navigation') }}</h3>
                <p>{{ __('statamic::messages.getting_started_widget_navigation') }}</p>
            </div>
        </a>
    </div>
</div>
