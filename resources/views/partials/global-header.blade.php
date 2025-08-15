@php
    use function Statamic\trans as __;
@endphp

<header class="h-14 bg-gray-800 flex justify-between space-x-2 items-center text-white px-4 dark fixed overflow-x-auto top-0 inset-x-0 z-[3]">
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 motion-safe:transition-all motion-safe:ease-[var(--animation-timing-function-fast-out-slow-in)] rounded-md" href="#main">
        {{ __('Skip to sidebar') }}
    </a>
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 motion-safe:transition-all motion-safe:duration-200 motion-safe:ease-[var(--animation-timing-function-fast-out-slow-in)] rounded-md" href="#main-content">
        {{ __('Skip to content') }}
    </a>
        <div class="flex items-center gap-2 text-[0.8125rem] text-gray-300">
            {{-- Logo --}}
            @if ($customDarkLogo)
                <button class="flex items-center group cursor-pointer text-gray-300 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
                    <div class="p-1 size-7 inset-0 flex items-center justify-center">
                        @cp_svg('icons/burger-menu', 'size-5')
                    </div>
                </button>
                {{-- Mobile nav toggle --}}
                <button class="flex items-center group cursor-pointer text-gray-300 hover:text-white md:hidden" type="button" @click="toggleMobileNav" aria-label="{{ __('Toggle Mobile Nav') }}">
                    <div class="p-1 size-7 inset-0 flex items-center justify-center">
                        @cp_svg('icons/burger-menu', 'size-5')
                    </div>
                </button>
            <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="max-w-[260px] max-h-9">
        @else
        <div class="flex items-center gap-2 relative">
            <button class="flex items-center group rounded-full cursor-pointer" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}" style="--focus-outline-offset: 0.2rem;">
                <div class="opacity-0 group-hover:opacity-100 p-1 size-7 transition-opacity duration-150 absolute inset-0 flex items-center justify-center">
                    @cp_svg('icons/burger-menu', 'size-5')
                </div>
                @cp_svg('ui/statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')
            </button>
            <a href="{{ route('statamic.cp.index') }}" class="hidden sm:block text-gray-300 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ $customLogoText ?? config('app.name') }}
            </a>
            @if (Statamic::pro())
                <ui-badge size="sm" variant="flat" text="Pro" class="hidden sm:block select-none dark:bg-gray-700/55!" />
            @endif
        </div>
        @endif

        <div class="items-center gap-2 hidden md:flex" data-global-header-breadcrumbs>
            @foreach($breadcrumbs as $breadcrumb)
                <span class="text-gray-500">/</span>
                <ui-button href="{{ $breadcrumb->url() }}" text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost"></ui-button>
                @if($breadcrumb->hasLinks() || $breadcrumb->createUrl())
                    <ui-dropdown v-cloak class="relative">
                        <template #trigger>
                            <ui-button variant="ghost" icon="ui/chevron-vertical" class="[&_svg]:size-3! h-8! w-4! hover:bg-gray-300/5! -ml-3 mr-1"></ui-button>
                        </template>
                        <ui-dropdown-header
                            class="grid grid-cols-[auto_1fr_auto] items-center"
                            icon="{{ $breadcrumb->icon() }}"
                            @if($breadcrumb->hasConfigureUrl())
                                append-icon="cog-solid"
                                append-href="{{ $breadcrumb->configureUrl() }}"
                            @endif
                        >
                            <a href="{{ $breadcrumb->url() }}">
                                {{ __($breadcrumb->text()) }}
                            </a>
                        </ui-dropdown-header>
                        @if($breadcrumb->hasLinks())
                            <ui-dropdown-menu>
                                @foreach($breadcrumb->links() as $link)
                                    <ui-dropdown-item
                                        text="{{ __($link->text) }}"
                                        icon="{{ $link->icon }}"
                                        href="{{ $link->url }}"
                                    ></ui-dropdown-item>
                                @endforeach
                            </ui-dropdown-menu>
                        @endif
                        @if($breadcrumb->createUrl())
                            <ui-dropdown-footer icon="plus" text="{{ __($breadcrumb->createLabel()) }}" href="{{ $breadcrumb->createUrl() }}"></ui-button>
                        @endif
                    </ui-dropdown>
                @endif
            @endforeach
        </div>
    </div>

    <div class="flex-1 flex gap-1 md:gap-4 items-center justify-end shrink-0">
        @if (Statamic\Facades\Site::authorized()->count() > 1)
            <global-site-selector></global-site-selector>
        @endif
        <div class="flex items-center"><command-palette /></div>
        <ui-command-palette-item
            text="{{ __('View Site') }}"
            icon="visit-website"
            url="{{ Statamic\Facades\Site::selected()->url() }}"
            open-new-tab
            v-slot="{ text, url, icon }"
        >
            <ui-button
                :icon="icon"
                class="[&_svg]:size-4 -me-3"
                variant="ghost"
                :href="url"
                target="_blank"
                v-tooltip="text"
                :aria-label="text"
            ></ui-button>
        </ui-command-palette-item>
        <x-statamic::user-dropdown />
    </div>
</header>
