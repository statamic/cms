<div class="global-header">
    <div class="lg:min-w-xl pl-2 md:pl-6 h-full flex items-center">
        <button class="nav-toggle hidden md:block ml-1 shrink-0" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">@cp_svg('icons/light/burger')</button>
        <button class="nav-toggle md:hidden ml-1 shrink-0" @click="toggleMobileNav" v-if="! mobileNavOpen" aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('icons/light/burger')</button>
        <button class="nav-toggle md:hidden ml-1 shrink-0" @click="toggleMobileNav" v-else v-cloak aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('icons/light/close')</button>
        <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
            <div v-tooltip="version" class="hidden md:block shrink-0">
                @if ($customLogo)
                    <img src="{{ $customLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo">
                @else
                    @cp_svg('statamic-wordmark', 'w-24')
                    @if (Statamic::pro())<span class="font-bold text-4xs align-top">PRO</span>@endif
                @endif
            </div>
        </a>
    </div>

    <div class="sm:px-8 w-full flex-1 lg:flex items-center lg:justify-center mx-auto max-w-full">
        <global-search ref="globalSearch" endpoint="{{ cp_route('search') }}" placeholder="{{ __('Search...') }}">
        </global-search>
    </div>

    <div class="head-link h-full md:pr-6 flex items-center justify-end">

        @if (Statamic\Facades\Site::hasMultiple())
            <global-site-selector>
                <template slot="icon">@cp_svg('icons/light/sites')</template>
            </global-site-selector>
        @endif

        <favorite-creator class="hidden md:block"></favorite-creator>

        @if (Route::has('horizon.index') && \Laravel\Horizon\Horizon::check(request()))
            <a class="hidden md:block h-6 w-6 p-1 text-gray ml-4 hover:text-gray-800" href="{{ route('horizon.index') }}" target="_blank" v-tooltip="'Laravel Horizon'">
                @cp_svg('icons/regular/horizon')
            </a>
        @endif

        @if (config('nova.path') && (app()->environment('local') || $user->can('viewNova')))
            <a class="hidden md:block h-6 w-6 p-1 text-gray ml-4 hover:text-gray-800" href="/{{ trim(config('nova.path'), '/') }}/dashboards/main" target="_blank" v-tooltip="'Laravel Nova'">
                @cp_svg('icons/regular/nova')
            </a>
        @endif

        @if (Route::has('telescope') && \Laravel\Telescope\Telescope::check(request()))
            <a class="hidden md:block h-6 w-6 p-1 text-gray ml-4 hover:text-gray-800" href="{{ route('telescope') }}" target="_blank" v-tooltip="'Laravel Telescope'">
                @cp_svg('icons/regular/telescope')
            </a>
        @endif

        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="hidden md:block h-6 w-6 ml-4 p-1 text-gray hover:text-gray-800" v-tooltip="__('Preferences')" aria-label="{{ __('Manage Preferences') }}">
                    @cp_svg('icons/light/cog')
                </button>
            </template>
            <dropdown-item :text="__('Preferences')" redirect="{{ route('statamic.cp.preferences.index') }}"></dropdown-item>
            <dropdown-item :text="__('CP Nav')" redirect="{{ route('statamic.cp.preferences.nav.index') }}"></dropdown-item>
        </dropdown-list>

        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="hidden md:block h-6 w-6 ml-4 p-1 text-gray hover:text-gray-800" v-tooltip="__('Useful Links')" aria-label="{{ __('View Useful Links') }}">
                    @cp_svg('icons/light/book-open')
                </button>
            </template>

            @if (config('statamic.cp.link_to_docs'))
            <dropdown-item external-link="https://statamic.dev" class="flex items-center">
                <span>{{ __('Documentation') }}</span>
                <i class="w-3 block ml-2">@cp_svg('icons/light/external-link')</i>
            </dropdown-item>
            @endif

            @if (config('statamic.cp.support_url'))
            <dropdown-item external-link="{{ config('statamic.cp.support_url') }}" class="flex items-center">
                <span>{{ __('Support') }}</span>
                <i class="w-3 block ml-2">@cp_svg('icons/light/external-link')</i>
            </dropdown-item>
            @endif

            <dropdown-item @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                <span>{{ __('Keyboard Shortcuts') }}</span>
            </dropdown-item>
        </dropdown-list>

        <a class="hidden md:block h-6 w-6 p-1 text-gray ml-4 hover:text-gray-800" href="{{ Statamic\Facades\Site::selected()->url() }}" target="_blank" v-tooltip="'{{ __('View Site') }}'" aria-label="{{ __('View Site') }}">
            @cp_svg('icons/light/browser-com')
        </a>
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="dropdown-toggle items-center ml-4 h-full hide flex">
                    @if ($user->avatar())
                        <div class="icon-header-avatar"><img src="{{ $user->avatar() }}" /></div>
                    @else
                        <div class="icon-header-avatar icon-user-initials">{{ $user->initials() }}</div>
                    @endif
                </a>
            </template>

            <div class="px-2">
                <div class="text-base mb-px">{{ $user->email() }}</div>
                @if ($user->isSuper())
                    <div class="text-2xs mt-px text-gray-600">{{ __('Super Admin') }}</div>
                @endif
            </div>
            <div class="divider"></div>

            <dropdown-item :text="__('Profile')" redirect="{{ route('statamic.cp.account') }}"></dropdown-item>
            <dropdown-item :text="__('Log out')" redirect="{{ route('statamic.cp.logout') }}"></dropdown-item>
        </dropdown-list>
    </div>
</div>

<div v-if="$refs.globalSearch?.focused" v-cloak class="fixed inset-0 h-full w-full bg-black/10 z-2"></div>
