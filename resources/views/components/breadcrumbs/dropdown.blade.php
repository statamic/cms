<ui-dropdown class="relative" aria-label="{{ __('More options for') }} {{ __($breadcrumb->text()) }}">
    <template #trigger>
        <ui-button
            variant="ghost"
            icon="chevron-vertical"
            class="[&_svg]:size-3! h-8! w-4! hover:bg-gray-300/5! -ml-3 mr-1"
            :aria-label="'{{ __('Options for') }} {{ __($breadcrumb->text()) }}'"
            aria-haspopup="true"
            aria-expanded="false"
        ></ui-button>
    </template>
    <ui-dropdown-header
        class="grid grid-cols-[auto_1fr_auto] items-center"
        icon="{{ $breadcrumb->icon() }}"
        @if($breadcrumb->hasConfigureUrl())
            append-icon="cog-solid"
        append-href="{{ $breadcrumb->configureUrl() }}"
        @endif
        role="menuitem"
    >
        <a href="{{ $breadcrumb->url() }}" aria-label="{{ __('Navigate to') }} {{ __($breadcrumb->text()) }}">
            {{ __($breadcrumb->text()) }}
        </a>
    </ui-dropdown-header>
    @if($breadcrumb->hasLinks())
        <ui-dropdown-menu role="menu">
            @foreach($breadcrumb->links() as $link)
                <ui-dropdown-item
                    text="{{ __($link->text) }}"
                    icon="{{ $link->icon }}"
                    href="{{ $link->url }}"
                    role="menuitem"
                    :aria-label="'{{ __($link->text) }} - {{ __('Navigate to') }}'"
                ></ui-dropdown-item>
            @endforeach
        </ui-dropdown-menu>
    @endif
    @if($breadcrumb->createUrl())
        <ui-dropdown-footer
            icon="plus"
            text="{{ __($breadcrumb->createLabel()) }}"
            href="{{ $breadcrumb->createUrl() }}"
            role="menuitem"
            :aria-label="'{{ __($breadcrumb->createLabel()) }} - {{ __('Create new') }}'"
        ></ui-dropdown-footer>
    @endif
</ui-dropdown>
