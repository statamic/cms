@php
    use function Statamic\trans as __;
@endphp

<ui-card-panel
    heading="{{ __('statamic::messages.getting_started_widget_header') }}"
    subheading="{{ __('statamic::messages.getting_started_widget_intro') }}"
>
    <div class="grid lg:grid-cols-2">
        <a href="https://statamic.dev" class="super-btn">
            @cp_svg('icons/light/book-pages')
            <div class="flex-1">
                <ui-heading size="lg">{{ __('Read the Documentation') }}</ui-heading>
                <ui-subheading>{{ __('statamic::messages.getting_started_widget_docs') }}</ui-subheading>
            </div>
        </a>
        @if (! Statamic::pro())
            <a href="https://statamic.dev/licensing" class="super-btn">
                @cp_svg('icons/light/pro-ribbon')
                <div class="flex-1">
                    <ui-heading size="lg">{{ __('Enable Pro Mode') }}</ui-heading>
                    <ui-subheading>{{ __('statamic::messages.getting_started_widget_pro') }}</ui-subheading>
                </div>
            </a>
        @endif

        <a href="{{ cp_route('collections.create') }}" class="super-btn">
            @cp_svg('icons/light/content-writing')
            <div class="flex-1">
                <ui-heading size="lg">{{ __('Create a Collection') }}</ui-heading>
                <ui-subheading>{{ __('statamic::messages.getting_started_widget_collections') }}</ui-subheading>
            </div>
        </a>
        <a href="{{ cp_route('blueprints.index') }}" class="super-btn">
            @cp_svg('icons/light/blueprints')
            <div class="flex-1">
                <ui-heading size="lg">{{ __('Create a Blueprint') }}</ui-heading>
                <ui-subheading>{{ __('statamic::messages.getting_started_widget_blueprints') }}</ui-subheading>
            </div>
        </a>
        <a href="{{ cp_route('navigation.create') }}" class="super-btn">
            @cp_svg('icons/light/hierarchy-files')
            <div class="flex-1">
                <ui-heading size="lg">{{ __('Create a Navigation') }}</ui-heading>
                <ui-subheading>{{ __('statamic::messages.getting_started_widget_navigation') }}</ui-subheading>
            </div>
        </a>
    </div>
</ui-card-panel>
