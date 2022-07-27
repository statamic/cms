<!doctype html>
<html lang="{{ config('app.locale') }}" dir="{{ Statamic\Facades\Site::selected()->direction() }}">
<head>
    @include('statamic::partials.head')
</head>

<body>
      <div id="statamic" class="statamic-inside" :style="{ marginRight: panes.length ? `24rem` : null }">

            @include('statamic::partials.session-expiry')
            @include('statamic::partials.licensing-alerts')
            @include('statamic::partials.global-header')

            @include('statamic::partials.nav-main')
            
            <main id="main"
                  class="flex flex-col @yield('content-class')"
                  :class="{
                        'nav-closed': ! navOpen,
                        'showing-license-banner': showBanner
                  }">

                  <div class="page-wrapper flex flex-col h-full w-full overflow-hidden" :class="wrapperClass">
                        @yield('content')
                  </div>

                  <portal-target name="outside"></portal-target>

            </main>

            <div id="overlays">

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

                  <portal-target name="live-preview"></portal-target>

                  <stacks v-if="stacks.length"></stacks>

                  <portal-target
                        v-for="(modal, i) in modals"
                        :key="`modal-${modal}-${i}`"
                        :name="`modal-${i}`"
                  ></portal-target>

                  <portal-target name="pane" :slim="true"></portal-target>

            </div>

      </div>

@include('statamic::partials.scripts')
@yield('scripts')

</body>
</html>
