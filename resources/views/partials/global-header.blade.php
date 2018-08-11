<div class="cp-head">

    <div class="logo">
        <a href="{{ route('cp') }}" class="flex items-center">
              @svg('statamic-wordmark')
              <span class="version" v-cloak>@{{ version }}</span>
        </a>
    </div>

    <global-search
        class="flex-1"
        endpoint="{{ route('search.global') }}"
        :limit="10"
    ></global-search>

    <div class="head-links pl-1 flex items-center">
        <a href="{{ route('site') }}" target="_blank" v-cloak v-tip :tip-text="translate('cp.view_site')">
            <span class="icon icon-popup"></span>
        </a>

        <a class="dropdown-toggle ml-1 hide md:block" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            {{-- @if (\Statamic\API\Config::get('users.enable_gravatar'))
                <img src="{{ \Statamic\API\User::getCurrent()->getAvatar() }}" alt="" height="32" width="32" class="round ml-8 z-depth-1">
            @else
                <div class="icon-user-initials round ml-8 z-depth-1">{{ \Statamic\API\User::getCurrent()->userInitials() }}</div>
            @endif --}}
            <div class="icon-user-initials rounded-full text-xxs bg-pink z-depth-1">ME</div>
        </a>
        <ul class="dropdown-menu hide md:block">
            <li><a href="{{ route('account') }}">{{ __('My Account') }}</a></li>
            <li><a href="{{ route('account.password') }}">{{ __('Change Password') }}</a></li>
            <li class="divider"></li>
            <li><a href="{{ route('logout') }}">{{ __('Sign Out') }}</a></li>
        </ul>
    </div>

</div>
