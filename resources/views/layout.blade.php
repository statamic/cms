<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body
        class="bg-global-header-bg dark:bg-dark-global-header-bg font-sans leading-normal text-gray-900 dark:text-white"
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
                    }"
                >
                    <main id="main" class="flex bg-body-bg dark:bg-dark-body-bg dark:border-t rounded-t-2xl dark:border-dark-body-border fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
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
