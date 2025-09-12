@php
    use function Statamic\trans as __;
@endphp

@inject('licenses', 'Statamic\Licensing\LicenseManager')

<header style="view-transition-name: header" class="h-14 bg-global-header-bg dark:bg-dark-global-header-bg flex justify-between space-x-2 items-center text-white px-4 fixed overflow-x-auto top-0 inset-x-0 z-[3]">
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main">
        {{ __('Skip to sidebar') }}
    </a>
    <a class="c-skip-link z-(--z-index-header) px-4 py-2 bg-blue-800 text-sm top-2.5 left-2.25 fixed opacity-0 -translate-y-24 focus:translate-y-0 focus:opacity-100 rounded-md" href="#main-content">
        {{ __('Skip to content') }}
    </a>
    <div class="dark flex items-center gap-2 text-[0.8125rem] text-white/85">
         {{-- Logo --}}
        @if ($customDarkLogo)
            <button class="flex items-center group cursor-pointer text-white/85 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
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
                @cp_svg('statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')
            </button>
            <a href="{{ route('statamic.cp.index') }}" class="hidden sm:block text-white/85 rounded-xs whitespace-nowrap" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ $customLogoText ?? config('app.name') }}
            </a>
            @if (Statamic::pro())
                @if ($licenses->valid())
                    <ui-badge size="sm" variant="flat" text="{{ __('Pro') }}" class="hidden sm:block select-none bg-white/15!" />
                @else
                    <ui-tooltip :text="{{ $licenses->requestFailed() ? "'".$licenses->requestFailureMessage()."'" : 'null' }}">
                        <ui-badge
                            @if ($licenses->requestFailed())
                                color="yellow"
                                icon="alert-warning-exclamation-mark"
                            @elseif ($licenses->isOnPublicDomain())
                                color="red"
                            @else
                                color="green"
                            @endif
                            href="{{ cp_route('utilities.licensing') }}"
                            text="{{ __('Pro') }} – {{ $licenses->isOnPublicDomain() ? __('statamic::messages.licensing_error_unlicensed') : __('Trial Mode') }}"
                        ></ui-badge>
                    </ui-tooltip>
                @endif
            @endif
        </div>
        @endif

        <div class="items-center gap-2 hidden md:flex" data-global-header-breadcrumbs v-cloak>
            @foreach($breadcrumbs as $breadcrumb)
                <div class="items-center gap-2 md:flex entry-animate-in entry-animate-in--quick">
                <span class="text-white/30">/</span>
                <ui-button href="{{ $breadcrumb->url() }}" text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost" class="dark:text-white/85! hover:text-white! px-2! mr-1.75"></ui-button>
                @if($breadcrumb->hasLinks() || $breadcrumb->createUrl())
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
                            ></ui-button>
                        @endif
                    </ui-dropdown>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    <div class="dark flex-1 flex gap-1 md:gap-3 items-center justify-end shrink-0 entry-animate-in entry-animate-in--quick">
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
                :aria-label="text"
                :href="url"
                :icon="icon"
                class="[&_svg]:size-4 -me-2 [&_svg]:text-white/85!"
                size="sm"
                target="_blank"
                v-tooltip="text"
                variant="ghost"
            ></ui-button>
        </ui-command-palette-item>
        <x-statamic::user-dropdown />
    </div>
</header>
