<!DOCTYPE html>
<html
    lang="{{ Statamic::cpLocale() }}"
    dir="{{ Statamic::cpDirection() }}"
>
    <head>
        @include('statamic::partials.head')
    </head>

    <body
        @if ($user && $user->getPreference('strict_accessibility')) data-contrast="increased" @endif
    >
        <div
            id="statamic"
            data-page="{{ json_encode($page ?? [
                'url' => '/'.request()->path(),
                'component' => 'NonInertiaPage',
                'version' => inertia()->getVersion(),
            ]) }}"
        >
            <div id="blade-title" data-title="
                @yield('title', $title ?? __('Here')) {{ Statamic::cpDirection() === 'ltr' ? '‹' : '›' }}
                {{ __(Statamic::pro() ? config('statamic.cp.custom_cms_name', 'Statamic') : 'Statamic') }}
            "></div>
            @yield('content')
        </div>

        {{--
        <div id="statamic">
           <config-provider>
                @include('statamic::partials.licensing-alerts')
                 @include('statamic::partials.global-header')

                <div
                    class="pt-14"
                    :class="{
                        'nav-closed': ! navOpen,
                        'nav-open': navOpen,
                    }"
                >
                    <main id="main" class="flex bg-body-bg dark:bg-dark-body-bg dark:border-t rounded-t-2xl dark:border-dark-body-border fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
                        @include('statamic::partials.nav-main')
                        <div id="main-content" class="main-content p-2 h-full flex-1 overflow-y-auto rounded-t-2xl">
                            <div class="relative content-card @yield('content-card-modifiers') min-h-full">
                                @yield('content')
                            </div>
                        </div>
                    </main>
                </div>
            </config-provider>
        </div>
        --}}

        @include('statamic::partials.scripts')
        @yield('scripts')
    </body>
</html>
