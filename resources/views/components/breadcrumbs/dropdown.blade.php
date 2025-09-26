{{-- Hardcoded duplicate of the dropdown trigger in blade to prevent Vue pop in --}}
<button
    :class="{'absolute right-0 animate-out fade-out duration-700 fill-mode-forwards':true}"
    class="
        inline-flex items-center justify-center whitespace-nowrap shrink-0
        font-medium antialiased cursor-pointer no-underline
        disabled:text-white/60 dark:disabled:text-white/50 disabled:cursor-not-allowed
        [&_svg]:shrink-0 [&_svg]:text-black [&_svg]:opacity-60 dark:[&_svg]:text-white
        bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200
        h-10 text-sm rounded-lg px-0 gap-0 w-10
        [&_svg]:size-4.5 [&_svg]:size-3! h-8! w-4! hover:bg-gray-300/5! -ml-3 mr-1
    "
    type="button"
    data-ui-dropdown-trigger
    tabindex="-1"
>
    @cp_svg('icons/chevron-vertical')
</button>

<ui-dropdown v-cloak class="relative" aria-label="{{ __('More options for') }} {{ __($breadcrumb->text()) }}">
    <template #trigger>
        <ui-button
            variant="ghost"
            icon="chevron-vertical"
            class="[&_svg]:size-3! h-8! w-4! hover:bg-gray-300/5! -ml-3 mr-1 animate-in fade-in duration-500"
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
