@php use function Statamic\trans as __; @endphp

<div class="global-header">
    <div class="lg:min-w-xl rtl:pr-2 ltr:pl-2 rtl:md:pr-6 ltr:md:pl-6 h-full flex items-center">
        <button class="nav-toggle hidden md:flex rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">@cp_svg('icons/light/burger', 'h-4 w-4')</button>
        <button class="nav-toggle md:hidden rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleMobileNav" v-if="! mobileNavOpen" aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('icons/light/burger', 'h-4 w-4')</button>
        <button class="nav-toggle md:hidden rtl:mr-1 ltr:ml-1 shrink-0" @click="toggleMobileNav" v-else v-cloak aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('icons/light/close', 'h-3 w-3')</button>
        <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
            <div v-tooltip="version" class="hidden md:block shrink-0">
                @if ($customLogo)
                    <img src="{{ $customLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo dark:hidden">
                    <img src="{{ $customDarkLogo }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo hidden dark:block">
                @elseif ($customLogoText)
                    <span class="font-medium">{{ $customLogoText }}</span>
                @else
                    @cp_svg('statamic-wordmark', 'w-24 logo')
                    @if (Statamic::pro())<span class="font-bold text-4xs align-top uppercase">{{ __('Pro') }}</span>@endif
                @endif
            </div>
        </a>
    </div>

    <div class="sm:px-8 w-full flex-1 lg:flex items-center lg:justify-center mx-auto max-w-full">
        <global-search ref="globalSearch" endpoint="{{ cp_route('search') }}" placeholder="{{ __('Search...') }}">
        </global-search>
    </div>

    <div class="head-link h-full px-6 space-x-3 rtl:space-x-reverse flex items-center justify-end">

        @if (Statamic\Facades\Site::authorized()->count() > 1)
            <global-site-selector>
                <template slot="icon">@cp_svg('icons/light/sites')</template>
            </global-site-selector>
        @endif

        <dark-mode-toggle initial="{{ $user->preferredTheme() }}"></dark-mode-toggle>

        <favorite-creator class="hidden md:block"></favorite-creator>

        @if (Route::has('horizon.index') && \Laravel\Horizon\Horizon::check(request()))
            <a class="global-header-icon-button hidden md:block" href="{{ route('horizon.index') }}" target="_blank" v-tooltip="'Laravel Horizon'">
                @cp_svg('icons/regular/horizon')
            </a>
        @endif

        @if (Route::has('pulse') && (app()->environment('local') || $user->can('viewPulse')))
            <a class="global-header-icon-button hidden md:block" href="{{ route('pulse') }}" target="_blank" v-tooltip="'Laravel Pulse'">
                @cp_svg('icons/regular/pulse')
            </a>
        @endif

        @if (config('nova.path') && (app()->environment('local') || $user->can('viewNova')))
            <a class="global-header-icon-button hidden md:block" href="/{{ trim(config('nova.path'), '/') }}/dashboards/main" target="_blank" v-tooltip="'Laravel Nova'">
                @cp_svg('icons/regular/nova')
            </a>
        @endif

        @if (Route::has('telescope') && \Laravel\Telescope\Telescope::check(request()))
            <a class="global-header-icon-button hidden md:block" href="{{ route('telescope') }}" target="_blank" v-tooltip="'Laravel Telescope'">
                @cp_svg('icons/regular/telescope')
            </a>
        @endif

        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="global-header-icon-button hidden md:block" v-tooltip="__('Useful Links')" aria-label="{{ __('View Useful Links') }}">
                    @cp_svg('icons/light/book-open')
                </button>
            </template>

            @if (config('statamic.cp.link_to_docs'))
            <dropdown-item external-link="https://statamic.dev" class="flex items-center">
                <span>{{ __('Documentation') }}</span>
                <i class="w-3 block rtl:mr-2 ltr:ml-2">@cp_svg('icons/light/external-link')</i>
            </dropdown-item>
            @endif

            @if (config('statamic.cp.support_url'))
            <dropdown-item external-link="{{ config('statamic.cp.support_url') }}" class="flex items-center">
                <span>{{ __('Support') }}</span>
                <i class="w-3 block rtl:mr-2 ltr:ml-2">@cp_svg('icons/light/external-link')</i>
            </dropdown-item>
            @endif

            <dropdown-item @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                <span>{{ __('Keyboard Shortcuts') }}</span>
            </dropdown-item>
        </dropdown-list>

        <a class="global-header-icon-button hidden md:block" href="{{ Statamic\Facades\Site::selected()->url() }}" target="_blank" v-tooltip="'{{ __('View Site') }}'" aria-label="{{ __('View Site') }}">
            @cp_svg('icons/light/browser-com')
        </a>
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="dropdown-toggle items-center h-full hide flex relative group">
                    @if ($user->avatar())
                        <div class="icon-header-avatar {{ session()->get('statamic_impersonated_by') ? 'animate-radar' : '' }}"><img src="{{ $user->avatar() }}" /></div>
                    @else
                        <div class="icon-header-avatar {{ session()->get('statamic_impersonated_by') ? 'animate-radar' : '' }} icon-user-initials">{{ $user->initials() }}</div>
                    @endif
                </a>
            </template>

            <div class="px-2">
                <div class="text-base mb-px">{{ $user->email() }}</div>
                @if ($user->isSuper())
                    <div class="text-2xs mt-px text-gray-600">{{ __('Super Admin') }} @if (session()->get('statamic_impersonated_by'))(Impersonating)@endif</div>
                @elseif (session()->get('statamic_impersonated_by'))
                    <div class="text-2xs mt-px text-gray-600">{{ __('Impersonating') }}</div>
                @endif
            </div>
            <div class="divider"></div>

            <dropdown-item :text="__('Profile')" redirect="{{ route('statamic.cp.account') }}"></dropdown-item>
            <dropdown-item :text="__('Preferences')" redirect="{{ cp_route('preferences.user.edit') }}"></dropdown-item>
            @if (session()->get('statamic_impersonated_by'))
                <dropdown-item :text="__('Stop Impersonating')" redirect="{{ cp_route('impersonation.stop') }}"></dropdown-item>
            @endif
            <dropdown-item :text="__('Log out')" redirect="{{ route('statamic.cp.logout', ['redirect' => cp_route('index')]) }}"></dropdown-item>
        </dropdown-list>
    </div>
</div>

<div v-if="$refs.globalSearch?.focused" v-cloak class="fixed inset-0 h-full w-full bg-black/10 z-2"></div>
