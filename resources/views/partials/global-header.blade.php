<div class="cp-head">

    <div class="logo">
        <a href="{{ route('statamic.cp.index') }}" class="flex items-center">
              @svg('statamic-wordmark')
              <span class="version" v-cloak>@{{ version }}</span>
        </a>
    </div>

    <global-search endpoint="{{ route('statamic.cp.search.global') }}" :limit="10" placeholder="{{ __('Search...') }}">
        <template slot="icon">
            @svg('search')
        </template>
    </global-search>

    <a>
        <span class="h-6 w-6 block p-sm mt-px ml-2">
            @svg('add-circle')
        </span>
    </a>

    <div class="head-links pl-1 flex items-center">
        <div class="dropdown">
            <a class="h-6 w-6 block p-sm text-grey hover:text-grey-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
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
        </div>
        <a class="h-6 w-6 block p-sm text-grey ml-2 hover:text-grey-dark" href="{{ route('site') }}" target="_blank">
            @svg('browser-com')
        </a>
        <div class="dropdown">
            <a class="dropdown-toggle ml-2 hide md:block" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="icon-user-initials rounded-full text-xxs bg-pink shadow">ME</div>
            </a>
            <ul class="dropdown-menu hide md:block">
                <li class="px-1">
                    <div class="text-base mb-px">dev@statamic.com</div>
                    <div class="text-xxs mt-px text-grey-light">Super Admin</div>
                </li>
                <li class="divider"></li>
                <li><a href="{{ route('statamic.cp.account') }}">{{ __('Profile') }}</a></li>
                <li><a href="{{ route('statamic.cp.account.password') }}">{{ __('Change Password') }}</a></li>
                <li><a href="{{ route('statamic.cp.logout') }}">{{ __('Logout') }}</a></li>
            </ul>
        </div>
    </div>

</div>
