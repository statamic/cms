<!doctype html>
<html lang="en">
<head>
      @include('partials.head')
</head>

<body id="statamic" :class="{ 'nav-visible': navVisible }">

      {{--  @if ($is_trial || $is_unlicensed)
            <div class="site-status-stripe flexy">
                  <div class="fill">
                        @if ($is_trial) {{ t('trial_mode_badge') }} @elseif ($is_unlicensed){{ t('unlicensed') }} @endif
                  </div>
                  <a href="{{ route('licensing') }}" class="btn btn-small mr-16">Add License Key</a>
                  <a href="https://statamic.com/buy" class="btn btn-primary btn-small" target="_blank">Buy Now</a>
            </div>
      @endif  --}}

      {!! inline_svg('sprite') !!}

      <nav class="nav-mobile">
          <a href="{{ route('cp') }}" class="logo">
              {!! svg('statamic-logo') !!}
          </a>
          <a @click.prevent="toggleNav" class="toggle">
              <span class="icon icon-menu"></span>
          </a>
      </nav>

      <div class="sneak-peek-wrapper">
            <div class="sneak-peek-viewport">
                  <i class="icon icon-circular-graph animation-spin"></i>
                  <div class="sneak-peek-resizer" @mousedown="sneakPeekResizeStart"></div>
                  <div class="sneak-peek-iframe-wrap" id="sneak-peek"></div>
            </div>
      </div>

      {{--  @include('partials.shortcuts')  --}}

      <div class="application-grid">

            <nav class="nav-main">
                  <a href="{{ route('cp') }}" class="logo">
                        {!! inline_svg('statamic-logo') !!}
                        <span class="version" v-cloak>@{{ version }}</span>
                  </a>
                  @include('partials.nav-main')
            </nav>

            <div class="content @yield('content-class')">

                  @include('partials.global-header')

                  {{--  @include('partials.alerts')  --}}

                  <div class="page-wrapper">
                        <div class="sneak-peek-header flexy">
                              <h1 class="fill">{{ trans('cp.sneak_peeking') }}</h1>
                              <button class="btn btn-primary" @click="stopPreviewing">{{ trans('cp.done') }}</button>
                        </div>
                        @yield('content')
                  </div>
            </div>

            <vue-toast v-ref:toast></vue-toast>
      </div>

      <script>
            {{--  Statamic.translations = {!! $translations !!};  --}}
            Statamic.permissions = '{!! $permissions !!}';
            Statamic.version = '{!! STATAMIC_VERSION !!}';

            @if(session()->has('success'))
                Statamic.flash = [{
                    type:    'success',
                    message: '{{ session()->get('success') }}',
                }];
            @endif
      </script>
      @include('partials.scripts')
      @yield('scripts')
</body>
</html>
