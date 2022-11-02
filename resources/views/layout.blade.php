<!doctype html>
<html lang="{{ config('app.locale') }}" dir="{{ Statamic\Facades\Site::selected()->direction() }}">
<head>
    @include('statamic::partials.head')
</head>

<body>
      <div id="statamic" :style="{ marginRight: panes.length ? `24rem` : null }">

      @include('statamic::partials.session-expiry')
      @include('statamic::partials.licensing-alerts')
      @include('statamic::partials.global-header')

      <div id="main"
            class="@yield('content-class')"
            :class="{
                  'nav-closed': ! navOpen,
                  'nav-mobile-open': mobileNavOpen,
                  'showing-license-banner': showBanner
            }">
            @include('statamic::partials.nav-main')
            @include('statamic::partials.nav-mobile')

            <div class="workspace">
                  <div class="page-wrapper" :class="wrapperClass">
                        @yield('content')
                  </div>
            </div>

            <component
                  v-for="component in appendedComponents"
                  :key="component.id"
                  :is="component.name"
                  v-bind="component.props"
                  v-on="component.events"
            ></component>

            <portal to="modals" v-if="showLoginModal">
                <login-modal
                      email="{{ $user->email() }}"
                      @closed="showLoginModal = false"
                ></login-modal>
            </portal>

            <keyboard-shortcuts-modal></keyboard-shortcuts-modal>

            <tooltip :pointer="true"></tooltip>

            <portal-targets></portal-targets>

            <portal-target name="live-preview"></portal-target>

            <portal-target name="pane" :slim="true"></portal-target>

            <portal-target name="outside"></portal-target>
      </div>

      {{-- @include('statamic::partials.nav-mobile') --}}

  </div>

@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
