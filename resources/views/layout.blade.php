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
          <a class="toggle">
              <span class="icon icon-menu"></span>
          </a>
      </nav>

      @include('statamic::partials.alerts')
      @include('statamic::partials.new-global-header')

      <div id="main" class="@yield('content-class')" :class="{'nav-closed': ! navOpen}">
            @include('statamic::partials.nav-main-new')

            <div class="content">
                  <div class="page-wrapper">
                        @yield('content')
                  </div>
            </div>

            <portal to="modals" v-if="showLoginModal">
                <login-modal
                      username="{{ \Statamic\API\User::getCurrent()->username() }}"
                      @closed="showLoginModal = false"
                ></login-modal>
            </portal>

            <keyboard-shortcuts-modal></keyboard-shortcuts-modal>

            <tooltip :pointer="true"></tooltip>

            <vue-toast ref="toast"></vue-toast>

            <portal-target name="modals"></portal-target>
      </div>
  </div>
@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
