<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
    class="{{ $user->preferredTheme() === 'dark' ? 'dark' : '' }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body>
        <div id="statamic">
            @include('statamic::partials.session-expiry')
            @include('statamic::partials.licensing-alerts')
            @include('statamic::partials.global-header')
            @include('statamic::partials.command-palette')

            <div
                id="main"
                class="@yield('content-class')"
                :class="{
                'nav-closed': ! navOpen,
                'nav-mobile-open': mobileNavOpen,
                'showing-license-banner': showBanner
            }"
            >
                @include('statamic::partials.nav-main')
                @include('statamic::partials.nav-mobile')

                <div class="workspace">
                    <div class="page-wrapper" :class="wrapperClass">
                        @yield('content')
                    </div>
                </div>
            </div>

            <component
                v-for="component in appendedComponents"
                :key="component.id"
                :is="component.name"
                v-bind="component.props"
                v-on="component.events"
            ></component>

            <confirmation-modal
                v-if="copyToClipboardModalUrl"
                :cancellable="false"
                :button-text="__('OK')"
                :title="__('Copy to clipboard')"
                @confirm="copyToClipboardModalUrl = null"
            >
                <div class="prose">
                    <code-block :text="copyToClipboardModalUrl" />
                </div>
            </confirmation-modal>

            <keyboard-shortcuts-modal></keyboard-shortcuts-modal>

            <portal-targets></portal-targets>
        </div>

        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
