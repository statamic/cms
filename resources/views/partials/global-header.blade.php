<div class="global-header">
    <div class="lg:min-w-xl pl-1 md:pl-3 h-full flex items-center">
        <button class="nav-toggle hidden md:block ml-sm flex-shrink-0" @click="toggleNav" aria-label="{{ __('Toggle Nav') }}">@cp_svg('burger')</button>
        <button class="nav-toggle md:hidden ml-sm flex-shrink-0" @click="toggleMobileNav" v-if="! mobileNavOpen" aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('burger')</button>
        <button class="nav-toggle md:hidden ml-sm flex-shrink-0" @click="toggleMobileNav" v-else v-cloak aria-label="{{ __('Toggle Mobile Nav') }}">@cp_svg('close')</button>
        <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
            <div v-tooltip="version" class="hidden md:block flex-shrink-0">
                @if (Statamic::pro() && config('statamic.cp.custom_logo_url'))
                    <img src="{{ config('statamic.cp.custom_logo_url') }}" alt="{{ config('statamic.cp.custom_cms_name') }}" class="white-label-logo">
                @else
                    @cp_svg('statamic-wordmark')
                    @if (Statamic::pro())<span class="font-bold text-4xs align-top">PRO</span>@endif
                @endif
            </div>
        </a>
    </div>

    <div class="sm:px-4 w-full flex-1 lg:flex items-center lg:justify-center mx-auto max-w-full">
        <global-search endpoint="{{ cp_route('search') }}" placeholder="{{ __('Search...') }}">
        </global-search>
    </div>

    <div class="head-link h-full md:pr-3 flex items-center justify-end">

        @if (Statamic\Facades\Site::hasMultiple())
            <global-site-selector>
                <template slot="icon">@cp_svg('sites')</template>
            </global-site-selector>
        @endif

        <favorite-creator class="hidden md:block"></favorite-creator>

        @if (config('telescope.enabled'))
            <a class="hidden md:block h-6 w-6 p-sm text-grey ml-2 hover:text-grey-80" href="/{{ config('telescope.path') }}" target="_blank" v-tooltip="'Laravel Telescope'">
                @cp_svg('telescope')
            </a>
        @endif
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <button class="hidden md:block h-6 w-6 ml-2 p-sm text-grey hover:text-grey-80" v-tooltip="__('Useful Links')" aria-label="{{ __('View Useful Links') }}">
                    @cp_svg('book-open')
                </button>
            </template>

            @if (config('statamic.cp.link_to_docs'))
            <dropdown-item external-link="https://statamic.dev" class="flex items-center">
                <span>{{__('Documentation')}}</span>
                <i class="w-3 block ml-1">@cp_svg('external-link')</i>
            </dropdown-item>
            @endif

            @if (config('statamic.cp.support_url'))
            <dropdown-item external-link="{{ config('statamic.cp.support_url') }}" class="flex items-center">
                <span>{{__('Support')}}</span>
                <i class="w-3 block ml-1">@cp_svg('external-link')</i>
            </dropdown-item>
            @endif

            <dropdown-item @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                <span>{{__('Keyboard Shortcuts')}}</span>
            </dropdown-item>
        </dropdown-list>

        <a class="hidden md:block h-6 w-6 p-sm text-grey ml-2 hover:text-grey-80" href="{{ Statamic\Facades\Site::selected()->url() }}" target="_blank" v-tooltip="'{{ __('View Site') }}'" aria-label="{{ __('View Site') }}">
            @cp_svg('browser-com')
        </a>
        <dropdown-list v-cloak>
            <template v-slot:trigger>
                <a class="dropdown-toggle items-center ml-2 h-full hide flex">
                    @if ($user->avatar())
                        <div class="icon-header-avatar"><img src="{{ $user->avatar() }}" /></div>
                    @else
                        <div class="icon-header-avatar icon-user-initials">{{ $user->initials() }}</div>
                    @endif
                </a>
            </template>

            <div class="px-1">
                <div class="text-base mb-px">{{ $user->email() }}</div>
                @if ($user->isSuper())
                    <div class="text-2xs mt-px text-grey-60">{{ __('Super Admin') }}</div>
                @endif
            </div>
            <div class="divider"></div>

            <dropdown-item :text="__('Profile')" redirect="{{ route('statamic.cp.account') }}"></dropdown-item>
            <dropdown-item :text="__('Log out')" redirect="{{ route('statamic.cp.logout') }}"></dropdown-item>
        </dropdown-list>
    </div>
</div>
