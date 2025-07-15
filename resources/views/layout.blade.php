<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body class="bg-gray-800 dark:bg-gray-950 font-sans leading-normal text-gray-900 dark:text-white">
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

                    <main id="main" class="
                    flex bg-gray-100 dark:bg-gray-900 dark:border-t rounded-t-2xl dark:border-white/10
                        fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]
                    ">
                        @include('statamic::partials.nav-main')
                        <div class="p-2 h-full flex-1 overflow-y-auto">
                            <div class="relative content-card @yield('content-card-modifiers') min-h-full transition-padding duration-300">
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
