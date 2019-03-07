<div class="global-header">
    <div class="w-54 pl-3 flex items-center">
        <button class="nav-toggle" @click="toggleNav">@svg('burger')</button>
        <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
            <div v-popover:tooltip.bottom="version">
                @svg('statamic-wordmark')
            </div>
        </a>
    </div>

    <global-search class="pl-2" endpoint="{{ cp_route('search') }}" :limit="10" placeholder="{{ __('Search...') }}">
    </global-search>

    <div class="head-link h-full px-3 flex items-center">

        @if (Statamic\API\Site::hasMultiple())
            <site-selector>
                <template slot="icon">@svg('sites')</template>
            </site-selector>
        @endif

        <favorite-creator
            current-url="{{ request()->fullUrl() }}"
        ></favorite-creator>

        @if (config('telescope.enabled'))
            <a class="h-6 w-6 block p-sm text-grey ml-2 hover:text-grey-80" href="/{{ config('telescope.path') }}" target="_blank" v-popover:tooltip.bottom="'Laravel Telescope'">
                @svg('telescope')
            </a>
        @endif

        <dropdown-list>
            <a class="h-6 w-6 block ml-2 p-sm text-grey hover:text-grey-80" slot="trigger">
                @svg('book-open')
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="https://docs.statamic.com" class="flex items-center">
                        <span>{{__('Documentation')}}</span>
                        <i class="w-3 block ml-1">@svg('expand')</i>
                    </a>
                </li>
                <li>
                    <a href="https://statamic.com/forum" class="flex items-center">
                        <span>{{ __('Support') }}</span>
                        <i class="w-3 block ml-1">@svg('expand')</i>
                    </a>
                </li>
                <li>
                    <a @click="$events.$emit('keyboard-shortcuts.open')" class="flex items-center">
                        <span>{{ __('Keyboard Shortcuts') }}</span>
                    </a>
                </li>
            </ul>
        </dropdown-list>
        <a class="h-6 w-6 block p-sm text-grey ml-2 hover:text-grey-80" href="{{ route('statamic.site') }}" target="_blank" v-popover:tooltip.bottom="'{{ __('View Site') }}'">
            @svg('browser-com')
        </a>
        <dropdown-list>
            <a class="dropdown-toggle ml-2 hide md:block" slot="trigger">
                @if (my()->avatar())
                    <div class="icon-header-avatar"><img src="{{ my()->avatar() }}" /></div>
                @else
                    <div class="icon-header-avatar icon-user-initials">{{ my()->initials() }}</div>
                @endif
            </a>
            <ul class="dropdown-menu hide md:block">
                <li class="px-1">
                    <div class="text-base mb-px">{{ my()->email() }}</div>
                    @if (me()->isSuper())
                        <div class="text-2xs mt-px text-grey-40">{{ __('Super Admin') }}</div>
                    @endif
                </li>
                <li class="divider"></li>
                <li><a href="{{ route('statamic.cp.account') }}">{{ __('Profile') }}</a></li>
                <li><a href="{{ route('statamic.cp.logout') }}">{{ __('Logout') }}</a></li>
            </ul>
        </dropdown-list>
    </div>
</div>
