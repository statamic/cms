<div class="cp-head">

    <typeahead v-ref:search :src="cp_url('search/perform')" :limit="10"></typeahead>

    <div class="head-links flexy">
        <a href="{{ route('site') }}" target="_blank" class="mr-8" v-cloak v-tip :tip-text="translate('view_site')">
            <span class="icon icon-popup"></span>
        </a>

        <a class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <img src="{{ gravatar(\Statamic\API\User::getCurrent()->email(), 64) }}" alt="" height="32" width="32" class="round ml-8 z-depth-1">
        </a>
        <ul class="dropdown-menu">
            <li><a href="{{ route('account') }}">{{ t('profile') }}</a></li>
            <li class="divider"></li>
            <li><a href="{{ route('logout') }}">{{ t('sign_out') }}</a></li>
        </ul>
    </div>

</div>
