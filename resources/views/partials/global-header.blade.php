@php
    use function Statamic\trans as __;
@endphp

<header
    class="dark fixed inset-x-0 top-0 z-[3] flex h-14 items-center justify-between space-x-2 bg-gray-800 px-4 text-white"
>
    <div class="flex items-center gap-2 text-[0.8125rem] text-gray-300">
        {{-- Logo --}}
        @if ($customDarkLogo)
            <button
                class="group flex cursor-pointer items-center text-gray-300 hover:text-white"
                type="button"
                @click="toggleNav"
                aria-label="{{ __("Toggle Nav") }}"
            >
                <div class="inset-0 flex size-7 items-center justify-center p-1">
                    @cp_svg("icons/burger-menu", "size-5")
                </div>
            </button>
            <img
                src="{{ $customDarkLogo }}"
                alt="{{ config("statamic.cp.custom_cms_name") }}"
                class="max-h-9 max-w-[260px]"
            />
        @else
            <div class="relative flex items-center gap-2">
                <button
                    class="group flex cursor-pointer items-center"
                    type="button"
                    @click="toggleNav"
                    aria-label="{{ __("Toggle Nav") }}"
                >
                    <div
                        class="absolute inset-0 flex size-7 items-center justify-center p-1 opacity-0 transition-opacity duration-150 group-hover:opacity-100"
                    >
                        @cp_svg("icons/burger-menu", "size-5")
                    </div>
                    @cp_svg("icons/statamic-mark-lime", "size-7 transition-opacity duration-150 group-hover:opacity-0")
                </button>
                <a href="{{ route("statamic.cp.index") }}" class="text-gray-300">
                    {{ $customLogoText ?? config("app.name") }}
                </a>
                @if (Statamic::pro())
                    <ui-badge size="sm" variant="flat" text="Pro" />
                @endif
            </div>
        @endif

        @foreach ($breadcrumbs as $breadcrumb)
            <span class="text-gray-500">/</span>
            @if ($breadcrumb->hasLinks() || $breadcrumb->createUrl())
                <ui-dropdown v-cloak>
                    <template #trigger>
                        <ui-button
                            text="{{ __($breadcrumb->text()) }}"
                            size="sm"
                            variant="ghost"
                            icon-append="ui/chevron-vertical"
                            class="[&_svg]:size-2"
                        />
                    </template>
                    <ui-dropdown-header
                        class="grid grid-cols-[auto_1fr_auto] items-center"
                        icon="{{ $breadcrumb->icon() }}"
                        @if ($breadcrumb->hasConfigureUrl())
                            append-icon="cog-solid"
                            append-href="{{ $breadcrumb->configureUrl() }}"
                        @endif
                    >
                        <a href="{{ $breadcrumb->url() }}">
                            {{ __($breadcrumb->text()) }}
                        </a>
                    </ui-dropdown-header>
                    @if ($breadcrumb->hasLinks())
                        <ui-dropdown-menu>
                            @foreach ($breadcrumb->links() as $link)
                                <ui-dropdown-item
                                    text="{{ __($link->text) }}"
                                    icon="{{ $link->icon }}"
                                    href="{{ $link->url }}"
                                ></ui-dropdown-item>
                            @endforeach
                        </ui-dropdown-menu>
                    @endif

                    @if ($breadcrumb->createUrl())
                        <ui-dropdown-footer
                            icon="plus"
                            text="{{ __($breadcrumb->createLabel()) }}"
                            href="{{ $breadcrumb->createUrl() }}"
                        />
                    @endif
                </ui-dropdown>
            @else
                <ui-button text="{{ __($breadcrumb->text()) }}" size="sm" variant="ghost" class="[&_svg]:size-2" />
            @endif
        @endforeach
    </div>
    <div class="flex flex-1 items-center justify-end gap-4">
        @if (Statamic\Facades\Site::authorized()->count() > 1)
            <global-site-selector>
                <template slot="icon">@cp_svg("icons/light/sites")</template>
            </global-site-selector>
        @endif

        <div><command-palette /></div>
        <ui-button
            icon="visit-website"
            class="-me-3 [&_svg]:size-4"
            variant="ghost"
            href="{{ Statamic\Facades\Site::selected()->url() }}"
            target="_blank"
            v-tooltip="'{{ __("View Site") }}'"
            aria-label="{{ __("View Site") }}"
        ></ui-button>
        <x-statamic::user-dropdown />
    </div>
</header>
