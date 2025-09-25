<script setup>
import Header from './Header.vue';
import Nav from './Nav.vue';
import { ConfigProvider } from 'reka-ui';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import { ref } from 'vue';

const navOpen = ref(true);
</script>

<template>
<!--    <div class="bg-white">-->
<!--        <Link href="/cp/dashboard">Dashboard</Link>-->
<!--        <Link href="/cp/collections/articles">Articles Collection</Link>-->
<!--        <slot />-->
<!--    </div>-->



    <ConfigProvider>
<!--        @include('statamic::partials.session-expiry')-->
<!--        @include('statamic::partials.licensing-alerts')-->
<!--        @include('statamic::partials.global-header')-->

        <Header />

        <div
            class="@yield('content-class') pt-14"
            :class="{
                        'nav-closed': ! navOpen,
                        'nav-open': navOpen,
                    }"
        >
            <main id="main" class="flex bg-body-bg dark:bg-dark-body-bg dark:border-t rounded-t-2xl dark:border-dark-body-border fixed top-14 inset-x-0 bottom-0 min-h-[calc(100vh-3.5rem)]">
<!--                @include('statamic::partials.nav-main')-->
                <Nav />
                <div id="main-content" class="main-content p-2 h-full flex-1 overflow-y-auto rounded-t-2xl">
                    <div class="relative content-card @yield('content-card-modifiers') min-h-full">
<!--                        @yield('content')-->
                        <slot />
                    </div>
                </div>
            </main>
        </div>

<!--        <component-->
<!--            v-for="component in appendedComponents"-->
<!--            :key="component.id"-->
<!--            :is="component.name"-->
<!--            v-bind="component.props"-->
<!--            v-on="component.events"-->
<!--        ></component>-->

<!--        <confirmation-modal-->
<!--            v-if="copyToClipboardModalUrl"-->
<!--            :cancellable="false"-->
<!--            :button-text="__('OK')"-->
<!--            :title="__('Copy to clipboard')"-->
<!--            @confirm="copyToClipboardModalUrl = null"-->
<!--        >-->
<!--            <div class="prose">-->
<!--                <ui-input :model-value="copyToClipboardModalUrl" readonly copyable class="font-mono text-sm dark" />-->
<!--            </div>-->
<!--        </confirmation-modal>-->

        <portal-targets></portal-targets>
    </ConfigProvider>
</template>
