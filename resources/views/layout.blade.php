<!DOCTYPE html>
<html lang="{{ Statamic::cpLocale() }}" dir="{{ Statamic::cpDirection() }}">
    <head>
        @include('statamic::partials.head')
    </head>

    <body class="bg-gray-800 font-sans leading-normal text-gray-800 dark:bg-gray-950 dark:text-white">
        <config-provider>
            <div id="statamic" v-cloak>
                @include('statamic::partials.session-expiry')
                @include('statamic::partials.licensing-alerts')
                @include('statamic::partials.global-header')

                <div
                    class="@yield('content-class') pt-14"
                    :class="{
                        'nav-closed': ! navOpen,
                        'nav-mobile-open': mobileNavOpen,
                        'showing-license-banner': showBanner
                    }"
                >
                    {{-- @include('statamic::partials.nav-mobile') --}}

                    <main
                        id="main"
                        class="fixed inset-x-0 top-14 bottom-0 flex min-h-[calc(100vh-3.5rem)] rounded-t-2xl bg-gray-100 dark:border-t dark:border-white/10 dark:bg-gray-900"
                    >
                        @include('statamic::partials.nav-main')
                        <div class="h-full flex-1 overflow-y-auto p-2">
                            <div
                                class="content-card @yield('content-card-modifiers') transition-padding relative min-h-full duration-300"
                            >
                                @yield('content')
                            </div>
                        </div>
                    </main>
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
        </config-provider>
    </body>
</html>
