<!doctype html>
{{-- TODO: Set lang with current translation --}}
<html lang="en">
<head>
      @include('statamic::partials.head')
</head>

<body>
    <div id="statamic">

      <nav class="nav-mobile">
          <a href="{{ route('statamic.cp.index') }}" class="logo">
              {!! svg('statamic-wordmark') !!}
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

      @include('statamic::partials.alerts')
      @include('statamic::partials.global-header')

      <div class="application-grid @yield('content-class')">
            @include('statamic::partials.nav-main-new')

            <div class="content">
                  <div class="page-wrapper">
                        <div class="sneak-peek-header flexy">
                              <h1 class="fill">{{ trans('cp.sneak_peeking') }}</h1>
                              <button class="btn btn-primary" @click="stopPreviewing">{{ trans('cp.done') }}</button>
                        </div>
                        @yield('content')
                  </div>
            </div>

            <portal to="modals" v-if="showLoginModal">
                <login-modal
                      username="{{ \Statamic\API\User::getCurrent()->username() }}"
                      @closed="showLoginModal = false"
                ></login-modal>
            </portal>

            <portal to="modals" v-if="showShortcuts">
                <shortcuts-modal
                    :show="showShortcuts"
                    @close="showShortcuts = false">
                </shortcuts-modal>
            </portal>

            <vue-toast ref="toast"></vue-toast>

            <portal-target name="modals"></portal-target>
      </div>
  </div>

<script>
    // Statamic.translations = {{-- $translations --}};
    Statamic.permissions = '{!! $permissions !!}';
    Statamic.version = '{!! STATAMIC_VERSION !!}';

    @if(session()->has('success'))
        Statamic.flash = [{
            type:    'success',
            message: '{{ session()->get('success') }}',
        }];
    @endif
</script>
@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
