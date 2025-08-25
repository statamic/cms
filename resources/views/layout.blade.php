<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
        <style>
            :root {
                /* --theme-color-primary: oklch(0.588 0.158 241.966); */
                --theme-color-primary: oklch(0.274 0.006 286.033);
                --theme-color-gray-50: oklch(0.985 0 0);
                --theme-color-gray-100: oklch(0.967 0.001 286.375);
                --theme-color-gray-200: oklch(0.92 0.004 286.32);
                --theme-color-gray-300: oklch(0.871 0.006 286.286);
                --theme-color-gray-400: oklch(0.705 0.015 286.067);
                --theme-color-gray-500: oklch(0.552 0.016 285.938);
                --theme-color-gray-600: oklch(0.442 0.017 285.786);
                --theme-color-gray-700: oklch(0.37 0.013 285.805);
                --theme-color-gray-800: oklch(0.274 0.006 286.033);
                --theme-color-gray-850: oklch(24.48% 0.0056 285.98);
                --theme-color-gray-900: oklch(0.21 0.006 285.885);
                --theme-color-gray-950: oklch(0.141 0.005 285.823);
                --theme-color-success: oklch(0.792 0.209 151.711);
                --theme-color-danger: oklch(0.577 0.245 27.325);
                --theme-color-body-bg: oklch(0.967 0.001 286.375);
                --theme-color-body-border: transparent;
                --theme-color-body-dark-bg: oklch(0.21 0.006 285.885);
                --theme-color-body-dark-border: oklch(0.141 0.005 285.823);
                --theme-color-content-bg: white;
                --theme-color-content-border: oklch(0.92 0.004 286.32);
                --theme-color-content-dark-bg: oklch(0.21 0.006 285.885);
                --theme-color-content-dark-border: oklch(0.141 0.005 285.823);
                --theme-color-global-header-bg: oklch(0.274 0.006 286.033);
            }
        </style>
    </head>

    <body
        class="bg-gray-800 font-sans leading-normal text-gray-900 dark:text-white"
        @if ($user->getPreference('strict_accessibility')) data-contrast="increased" @endif
    >
        <div id="statamic">
           <config-provider>
                @include('statamic::partials.session-expiry')
                @include('statamic::partials.licensing-alerts')
                 @include('statamic::partials.global-header')

                <div
                    class="@yield('content-class') pt-14"
                    :class="{
                        'nav-closed': ! navOpen,
                        'nav-open': navOpen,
                        'showing-license-banner': showBanner
                    }"
                >
                    <main id="main" class="flex bg-body-bg dark:bg-body-dark-bg dark:border-t rounded-t-2xl dark:border-body-dark-border fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
                        @include('statamic::partials.nav-main')
                        <div id="main-content" v-cloak class="main-content p-2 h-full flex-1 overflow-y-auto">
                            <div class="relative content-card @yield('content-card-modifiers') min-h-full">
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
                        <ui-input :model-value="copyToClipboardModalUrl" readonly copyable class="font-mono text-sm dark" />
                    </div>
                </confirmation-modal>

                <portal-targets></portal-targets>
            </config-provider>
        </div>

        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
