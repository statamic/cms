<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Card, CardPanel, Panel, Heading, Badge, DocsCallout, Modal, ModalClose, Description } from '@ui';

const props = defineProps([
    'requestError',
    'site',
    'statamic',
    'addons',
    'unlistedAddons',
    'configCached',
    'purchase',
    'primaryAction',
    'usingLicenseKeyFile',
    'refreshUrl',
    'mintUrl',
]);

const minting = ref(false);
const buyModalOpen = ref(false);
const awaitingReturn = ref(false);
const checking = ref(false);

function mint() {
    if (!props.mintUrl || minting.value) {
        return;
    }

    minting.value = true;
    router.post(props.mintUrl, {}, {
        onFinish: () => {
            minting.value = false;
        },
    });
}

function markOutbound() {
    awaitingReturn.value = true;
}

function maybeRefresh() {
    if (!awaitingReturn.value || checking.value) {
        return;
    }

    awaitingReturn.value = false;
    checking.value = true;
    router.visit(props.refreshUrl, {
        onFinish: () => {
            checking.value = false;
        },
    });
}

function onVisibilityChange() {
    if (document.visibilityState === 'visible') {
        maybeRefresh();
    }
}

function statusDotClass(valid) {
    return valid ? 'bg-green-500' : 'bg-red-500 dark:bg-red-600';
}

onMounted(() => {
    document.addEventListener('visibilitychange', onVisibilityChange);
    window.addEventListener('focus', maybeRefresh);
});

onUnmounted(() => {
    document.removeEventListener('visibilitychange', onVisibilityChange);
    window.removeEventListener('focus', maybeRefresh);
});
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[__('Licensing'), __('Utilities')]" />

        <Header :title="__('Licensing')" icon="license">
            <Button
                :href="refreshUrl"
                :text="checking ? __('Checking statamic.com…') : __('Refresh')"
                :disabled="checking"
                v-tooltip="__('statamic::messages.licensing_refresh_instructions')"
            />
            <Button
                v-if="primaryAction === 'mint'"
                variant="primary"
                :text="__('Generate site key')"
                :disabled="minting"
                @click="mint"
            />
            <Button
                v-if="primaryAction === 'connect'"
                :href="site.handoffUrl"
                target="_blank"
                variant="primary"
                :text="__('Connect to Statamic.com')"
                @click="markOutbound"
            />
            <Button
                v-if="primaryAction === 'buy'"
                variant="primary"
                :text="purchase.label"
                @click="buyModalOpen = true"
            />
            <Button
                v-if="primaryAction === 'domain'"
                :href="site.url"
                target="_blank"
                variant="primary"
                :text="__('Add domain on Statamic.com')"
                @click="markOutbound"
            />
        </Header>

        <Card v-if="requestError" class="w-full space-y-4 flex items-center justify-between">
            <Heading size="lg" class="mb-0!" :text="usingLicenseKeyFile ? __('statamic::messages.outpost_license_key_error') : __('statamic::messages.outpost_issue_try_later')" icon="warning-diamond" />
            <Button :href="refreshUrl" variant="primary">
                {{ __('Try Again') }}
            </Button>
        </Card>

        <section v-else class="space-y-6">
            <CardPanel v-if="configCached" :heading="__('Configuration is cached')">
                <p class="text-gray-700 text-sm" v-html="__('statamic::messages.licensing_config_cached_warning')" />
            </CardPanel>

            <CardPanel v-if="site.usesIncorrectKeyFormat" :heading="__('statamic::messages.licensing_incorrect_key_format_heading')">
                <p class="text-gray-700 text-sm" v-html="__('statamic::messages.licensing_incorrect_key_format_body')" />
            </CardPanel>

            <CardPanel v-if="site.hasSharedKey" :heading="__('Shared site key')">
                <p class="text-gray-700 text-sm" v-html="__('statamic::messages.licensing_shared_key')" />
            </CardPanel>

            <Panel :heading="__('Site')">
                <Card class="py-0!">
                    <div class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <div class="flex items-center gap-4 px-3 py-3">
                            <div class="flex flex-1 shrink-0 items-center gap-2 font-bold sm:gap-3">
                                <span class="little-dot" :class="statusDotClass(site.valid)" />
                                <a :href="site.url" target="_blank" class="truncate underline" @click="markOutbound">
                                    {{ site.name || site.key || __('No site key') }}
                                </a>
                                <Badge v-if="site.key" :color="site.connected ? 'green' : 'amber'" size="sm">
                                    {{ site.connected ? __('Connected') : __('Not connected') }}
                                </Badge>
                            </div>
                            <div class="shrink-0 text-end">
                                <ui-badge color="green" :prepend="__('Site Key')" v-if="site.key">{{ site.key }}</ui-badge>
                                <Badge v-if="site.invalidReason" color="red">
                                    {{ site.invalidReason }}
                                </Badge>
                            </div>
                        </div>
                        <div
                            v-for="domain in site.domains"
                            :key="domain.url"
                            class="flex items-center gap-4 px-3 py-3"
                        >
                            <div class="w-72 shrink-0 truncate ps-5">
                                {{ domain.url }}
                            </div>
                            <div class="min-w-0 flex-1" />
                            <div class="w-52 shrink-0 text-end">
                                <Badge v-if="domain.environment" :prepend="__('Environment')">
                                    {{ domain.environment === 'production' ? __('Production') : __('Testing') }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </Card>
            </Panel>

            <Panel :heading="__('Core')">
                <Card class="py-0!">
                    <div class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <div class="flex items-center gap-4 px-3 py-3">
                            <div class="flex w-72 shrink-0 items-center gap-2 font-bold sm:gap-3">
                                <span class="little-dot" :class="statusDotClass(statamic.valid)" />
                                <span>
                                    {{ __('Statamic') }}
                                    <span v-if="statamic.pro" class="text-pink">{{ __('Pro') }}</span>
                                    <template v-else>{{ __('Free') }}</template>
                                </span>
                            </div>
                            <div class="min-w-0 flex-1 truncate">{{ statamic.version }}</div>
                            <div class="w-52 shrink-0 text-end">
                                <Badge v-if="statamic.invalidReason" color="red">
                                    {{ statamic.invalidReason }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </Card>
            </Panel>

            <Panel v-if="addons.length" :heading="__('Addons')">
                <Card class="py-0!">
                    <div class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <div v-for="addon in addons" :key="addon.name" class="flex items-center gap-4 px-3 py-3">
                            <div class="flex w-72 shrink-0 items-center gap-2 sm:gap-3">
                                <span class="little-dot" :class="statusDotClass(addon.valid)" />
                                <a :href="addon.marketplaceUrl" class="truncate font-bold underline">{{ addon.name }}</a>
                                <Badge v-if="addon.edition">{{ addon.edition }}</Badge>
                            </div>
                            <div class="min-w-0 flex-1 truncate">{{ addon.version }}</div>
                            <div class="w-52 shrink-0 text-end">
                                <Badge v-if="addon.invalidReason" color="red">
                                    {{ addon.invalidReason }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </Card>
            </Panel>

            <Panel v-if="unlistedAddons.length" :heading="__('Unlisted Addons')">
                <Card class="py-0!">
                    <div class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <div v-for="addon in unlistedAddons" :key="addon.name" class="flex items-center gap-4 px-3 py-3">
                            <div class="flex w-72 shrink-0 items-center gap-2 sm:gap-3">
                                <span class="little-dot bg-green-500" />
                                <span class="truncate">{{ addon.name }}</span>
                            </div>
                            <div class="min-w-0 flex-1 truncate">{{ addon.version }}</div>
                            <div class="w-52 shrink-0" />
                        </div>
                    </div>
                </Card>
            </Panel>
        </section>

        <DocsCallout :topic="__('Licensing')" url="licensing" />

        <Modal
            v-if="purchase"
            v-model:open="buyModalOpen"
            :title="purchase.title"
            icon="license"
            blur
        >
            <div class="space-y-4">
                <Description :text="purchase.description" />

                <Card class="py-0!">
                    <div class="divide-y divide-gray-200 text-sm dark:divide-gray-700">
                        <div
                            v-for="item in purchase.items"
                            :key="item.name"
                            class="flex items-center gap-3 px-3 py-3"
                        >
                            <img
                                v-if="item.thumbnail"
                                :src="item.thumbnail"
                                :alt="item.name"
                                class="size-10 shrink-0 rounded object-cover"
                            >
                            <div class="min-w-0 flex-1">
                                <div class="font-medium text-gray-950 dark:text-gray-100">
                                    <a v-if="item.url" :href="item.url" target="_blank" class="underline">{{ item.name }}</a>
                                    <template v-else>{{ item.name }}</template>
                                </div>
                                <Description v-if="item.detail" :text="item.detail" />
                            </div>
                            <div v-if="item.price" class="shrink-0 font-medium text-gray-950 dark:text-gray-100">
                                {{ item.price }}
                            </div>
                        </div>
                    </div>
                </Card>
            </div>

            <template #footer>
                <div class="flex items-center justify-end space-x-3 pt-3 pb-1">
                    <ModalClose>
                        <Button variant="ghost" :text="__('Cancel')" />
                    </ModalClose>
                    <Button
                        variant="primary"
                        :href="purchase.checkoutUrl"
                        target="_blank"
                        :text="__('Checkout on statamic.com')"
                        @click="markOutbound(); buyModalOpen = false"
                    />
                </div>
            </template>
        </Modal>
    </div>
</template>
