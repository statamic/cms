<div class="global-header">
    <div class="flex items-center flex-1">
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
    </div>

    <div class="flex items-center px-2 border-l h-full text-sm">
        <dropdown-list>
            <button class="flex outline-none items-center dropdown-toggle anti text-grey hover:text-grey-dark" slot="trigger">
                <i class="block h-6 w-6 mr-1">@svg('sites')</i><span>English</span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="">English (active)</a></li>
                <li><a href="" class="text-grey">Canadian</a></li>
                <li><a href="" class="text-grey">French</a></li>
                <li><a href="" class="text-grey">German</a></li>
                <li><a href="" class="text-grey">Spanish</a></li>
            </ul>
        </dropdown-list>
    </div>

    <div class="flex items-center px-2 border-l h-full text-sm">
        <dropdown-list>
            <button class="flex outline-none items-center dropdown-toggle anti text-grey hover:text-grey-dark" slot="trigger">
                <i class="block h-6 w-6 mr-1">@svg('pin')</i><span>Shortcuts</span>
            </button>
            <ul class="dropdown-menu">
                <li><a href="">New Blog Post</a></li>
                <li><a href="">New Event</a></li>
                <li><a href="">New FAQ Question</a></li>
                <li><a href="">Edit Footer Links</a></li>
                <li><a href="">Manage Photo Galleries</a></li>
                <li class="divider"></li>
                <li><a href="" class="text-grey hover:text-white">Customize Shortcuts</a></li>
            </ul>
        </dropdown-list>
    </div>

    <div class="head-link border-l h-full px-3 flex items-center">
        <dropdown-list>
            <a class="h-6 w-6 block p-sm text-grey hover:text-grey-dark" slot="trigger">
                @svg('book-open')
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="https://docs.statamic.com" class="flex items-center">
                        <span>Documentation</span>
                        <i class="w-3 block ml-1">@svg('expand')</i>
                    </a>
                </li>
                <li>
                    <a href="https://statamic.com/forum" class="flex items-center">
                        <span>Support</span>
                        <i class="w-3 block ml-1">@svg('expand')</i>
                    </a>
                </li>
                <li>
                    <a @click="$modal.show('keyboard-shortcuts')" class="flex items-center">
                        <span>Keyboard Shortcuts</span>
                    </a>
                </li>
            </ul>
        </dropdown-list>
        <a class="h-6 w-6 block p-sm text-grey ml-2 hover:text-grey-dark" href="{{ route('statamic.site') }}" target="_blank" v-popover:tooltip.bottom="'{{ __('View Site') }}'">
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
                        <div class="text-2xs mt-px text-grey-light">{{ __('Super Admin') }}</div>
                    @endif
                </li>
                <li class="divider"></li>
                <li><a href="{{ route('statamic.cp.account') }}">{{ __('Profile') }}</a></li>
                <li><a href="{{ route('statamic.cp.logout') }}">{{ __('Logout') }}</a></li>
            </ul>
        </dropdown-list>
    </div>
</div>
