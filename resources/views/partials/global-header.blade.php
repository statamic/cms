@php
    use function Statamic\trans as __;
@endphp

<header class="h-14 bg-gray-800 flex justify-between space-x-2 items-center text-white px-4 dark fixed top-0 inset-x-0 z-[3]">
    <div class="flex items-center gap-2 text-[0.8125rem] text-gray-300">
        {{-- Logo --}}
        @if ($customDarkLogo)
            <button class="flex items-center group cursor-pointer text-gray-300 hover:text-white" type="button" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">
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
                @cp_svg('icons/statamic-mark-lime', 'size-7 group-hover:opacity-0 transition-opacity duration-150')
            </button>
            <a href="{{ route('statamic.cp.index') }}" class="text-gray-300 rounded-xs" style="--focus-outline-offset: var(--outline-offset-button);">
                {{ $customLogoText ?? config('app.name') }}
            </a>
            @if (Statamic::pro())
                <ui-badge size="sm" variant="flat" text="Pro" class="select-none" />
            @endif
        </div>
        @endif

        @foreach($breadcrumbs as $breadcrumb)
            <span class="text-gray-500">/</span>
            @if($breadcrumb->hasLinks() || $breadcrumb->createUrl())
                <ui-dropdown v-cloak>
                    <template #trigger>
                        <ui-button text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost" icon-append="ui/chevron-vertical" class="[&_svg]:size-2" />
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
                        <ui-dropdown-footer icon="plus" text="{{ __($breadcrumb->createLabel()) }}" href="{{ $breadcrumb->createUrl() }}" />
                    @endif
                </ui-dropdown>
            @else
                <ui-button text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost" class="[&_svg]:size-2" />
            @endif
        @endforeach
    </div>
    <div class="flex-1 flex gap-4 items-center justify-end">
        @if (Statamic\Facades\Site::authorized()->count() > 1)
            <global-site-selector>
                <template slot="icon">@cp_svg('icons/light/sites')</template>
            </global-site-selector>
        @endif
        <div><command-palette /></div>
        <ui-button
            icon="visit-website"
            class="[&_svg]:size-4 -me-3"
            variant="ghost"
            href="{{ Statamic\Facades\Site::selected()->url() }}"
            target="_blank"
            v-tooltip="'{{ __('View Site') }}'"
            aria-label="{{ __('View Site') }}"
        ></ui-button>
        <x-statamic::user-dropdown />
    </div>
</header>
