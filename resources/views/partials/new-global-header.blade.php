<div class="bg-white shadow h-12 mb-4 flex items-center justify-between">
    <div class="flex items-center flex-1">
        <div class="w-54 pl-3">
            <a href="{{ route('statamic.cp.index') }}" class="flex items-end">
                <div v-popover:tooltip.bottom="version">
                    @svg('statamic-wordmark')
                </div>
            </a>
        </div>

        <global-search class="pl-2" endpoint="{{ route('statamic.cp.search.global') }}" :limit="10" placeholder="{{ __('Search...') }}">
        </global-search>
    </div>

    <div class="flex items-center px-3 border-l h-full text-sm">
        <div class="dropdown">
            <button class="flex outline-none items-center dropdown-toggle anti text-grey-light hover:text-grey-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="block h-6 w-6 mr-1">@svg('new/content-pencil-write')</i><span>Shortcuts</span>
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
        </div>
    </div>

    <div class="head-link border-l h-full px-3 flex items-center">
        <div class="dropdown">
            <a class="h-6 w-6 block p-sm text-grey-light hover:text-grey-dark" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                @svg('new/book-open-text')
            </a>
            <ul class="dropdown-menu">
                <li>
                    <a href="https://docs.statamic.com" class="flex items-center">
                        <span>Documentation</span>
                        <i class="w-3 block ml-1">@svg('new/expand-6')</i>
                    </a>
                </li>
                <li>
                    <a href="https://statamic.com/forum" class="flex items-center">
                        <span>Support</span>
                        <i class="w-3 block ml-1">@svg('new/expand-6')</i>
                    </a>
                </li>
                <li>
                    <a @click="$modal.show('keyboard-shortcuts')" class="flex items-center">
                        <span>Keyboard Shortcuts</span>
                    </a>
                </li>
            </ul>
        </div>
        <a class="h-6 w-6 block p-sm text-grey-light ml-2 hover:text-grey-dark" href="{{ route('site') }}" target="_blank" v-popover:tooltip.bottom="'{{ __('View Site') }}'">
            @svg('new/browser-com')
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
