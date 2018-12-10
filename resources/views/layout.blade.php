<!doctype html>
{{-- TODO: Set lang with current translation --}}
<html lang="en">
<head>
    @include('statamic::partials.head')
</head>

<body>
    <div id="statamic">

      @include('statamic::partials.global-header')
      @include('statamic::partials.alerts')

      <div id="main" class="@yield('content-class')" :class="{'nav-closed': ! navOpen}">
            @include('statamic::partials.nav-main')

            <div class="content">
                  <div class="page-wrapper">
                        @yield('content')
                  </div>
            </div>

            <portal to="modals" v-if="showLoginModal">
                <login-modal
                      email="{{ \Statamic\API\User::getCurrent()->email() }}"
                      @closed="showLoginModal = false"
                ></login-modal>
            </portal>

            <keyboard-shortcuts-modal></keyboard-shortcuts-modal>

            <tooltip :pointer="true"></tooltip>

            <vue-toast ref="toast"></vue-toast>

            <portal-target name="modals"></portal-target>
      </div>

      @include('statamic::partials.nav-mobile')

  </div>

@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
