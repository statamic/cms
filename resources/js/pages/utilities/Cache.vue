<script setup>
import { Deferred, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { numberFormatter } from '@api';
import Head from '@/pages/layout/Head.vue';
import {
    Header, Button, Panel, PanelHeader, Heading, Card, Description, Badge, Text, Skeleton,
    DocsCallout, CommandPaletteItem, Dropdown, DropdownMenu, DropdownItem, Textarea,
    Field, Modal, ModalClose,
} from '@ui';

const props = defineProps([
    'stache',
    'cache',
    'static',
    'images',
    'operationResult',
    'clearAllUrl',
    'clearStacheUrl',
    'warmStacheUrl',
    'clearStaticUrl',
    'invalidatePagesUrl',
    'clearApplicationUrl',
    'clearImageUrl',
]);

const activeAction = ref(null);
const results = ref({});
const staticUrls = ref(null);
const invalidateStaticUrlsModal = ref(false);
const invalidateStaticUrlsError = ref(null);

function runAction(action, cache, url, options = {}) {
    if (activeAction.value) {
        return;
    }

    activeAction.value = action;
    delete results.value[cache];

    router.post(url, options.data ?? {}, {
        preserveScroll: true,
        onSuccess: (page) => {
            results.value[cache] = {
                type: 'success',
                message: page.props.operationResult,
            };

            options.onSuccess?.();
        },
        onError: (errors) => {
            results.value[cache] = {
                type: 'error',
                message: Object.values(errors).flat()[0],
            };

            options.onError?.(errors);
        },
        onFinish: () => {
            activeAction.value = null;
        },
    });
}

function clearAll() {
    runAction('clearAll', 'all', props.clearAllUrl, {
        onSuccess: () => {
            results.value = { all: results.value.all };
        },
    });
}

function rebuildStache() {
    runAction('rebuildStache', 'stache', props.clearStacheUrl);
}

function warmStache() {
    runAction('warmStache', 'stache', props.warmStacheUrl);
}

function clearStatic() {
    runAction('clearStatic', 'static', props.clearStaticUrl);
}

function clearApplication() {
    runAction('clearApplication', 'application', props.clearApplicationUrl);
}

function clearImage() {
    runAction('clearImage', 'images', props.clearImageUrl);
}

function openInvalidateStaticUrls() {
    if (activeAction.value) {
        return;
    }

    invalidateStaticUrlsError.value = null;
    invalidateStaticUrlsModal.value = true;
}

function invalidateStaticUrls() {
    invalidateStaticUrlsError.value = null;

    runAction('invalidateStaticUrls', 'static', props.invalidatePagesUrl, {
        data: { urls: staticUrls.value?.split('\n') },
        onSuccess: () => {
            staticUrls.value = null;
            invalidateStaticUrlsModal.value = false;
        },
        onError: (errors) => {
            invalidateStaticUrlsError.value = errors.urls;
        },
    });
}
</script>

<template>
    <Head :title="[__('Cache Manager'), __('Utilities')]" />

    <div class="max-w-page mx-auto">
        <Header :title="__('Cache Manager')" icon="cache">
            <CommandPaletteItem
                category="Actions"
                :text="__('Clear all caches')"
                icon="live-preview"
                :action="clearAll"
                :when="() => !activeAction"
                v-slot="{ text }"
            >
                <Button
                    :text="text"
                    :loading="activeAction === 'clearAll'"
                    :disabled="!!activeAction"
                    @click="clearAll"
                />
            </CommandPaletteItem>
        </Header>

        <Text
            v-if="results.all"
            as="p"
            :variant="results.all.type === 'error' ? 'danger' : 'success'"
            class="mb-6"
            role="status"
        >
            {{ results.all.message }}
        </Text>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex flex-wrap items-center justify-between gap-2 min-h-10">
                    <div class="flex min-w-0 items-center gap-1">
                        <Heading>{{ __('Content Stache') }}</Heading>
                        <Button
                            v-tooltip="__('statamic::messages.cache_utility_stache_description')"
                            icon="info-square"
                            icon-only
                            variant="subtle"
                            size="sm"
                            :aria-label="__('statamic::messages.cache_utility_stache_description')"
                            @click="$event.currentTarget.focus()"
                        >
                            <span
                                class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                                aria-hidden="true"
                            />
                        </Button>
                    </div>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Warm'), __('Content Stache')]"
                            icon="fire-flame-burn-hot"
                            :action="warmStache"
                            :when="() => !activeAction"
                        >
                            <Button
                                :text="__('Warm')"
                                variant="ghost"
                                size="sm"
                                :loading="activeAction === 'warmStache'"
                                :disabled="!!activeAction"
                                @click="warmStache"
                            />
                        </CommandPaletteItem>
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Rebuild'), __('Content Stache')]"
                            icon="live-preview"
                            :action="rebuildStache"
                            :when="() => !activeAction"
                        >
                            <Button
                                :text="__('Rebuild')"
                                size="sm"
                                :loading="activeAction === 'rebuildStache'"
                                :disabled="!!activeAction"
                                @click="rebuildStache"
                            />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <dl class="grid grid-cols-1 gap-6 pb-3 @sm/panel:grid-cols-2">
                        <div class="flex min-w-0 flex-col gap-2">
                            <dt>
                                <Description>{{ __('Records') }}</Description>
                            </dt>
                            <dd>
                                <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(stache.records) }}</Heading>
                            </dd>
                        </div>
                        <div v-if="stache.size" class="flex min-w-0 flex-col gap-2">
                            <dt>
                                <Description>{{ __('Size') }}</Description>
                            </dt>
                            <dd>
                                <Heading size="2xl" class="tabular-nums">{{ stache.size }}</Heading>
                            </dd>
                        </div>
                    </dl>
                    <div v-if="stache.time || stache.rebuilt" class="flex flex-wrap gap-2">
                        <Badge v-if="stache.time" :prepend="__('Build time')" class="tabular-nums">{{ stache.time }}</Badge>
                        <Badge v-if="stache.rebuilt" :prepend="__('Last rebuild')">{{ stache.rebuilt }}</Badge>
                    </div>
                    <Text
                        as="p"
                        :variant="results.stache?.type === 'error' ? 'danger' : 'success'"
                        class="min-h-5"
                        role="status"
                    >
                        {{ results.stache?.message }}
                    </Text>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex flex-wrap items-center justify-between gap-2 min-h-10">
                    <div class="flex min-w-0 items-center gap-1">
                        <Heading>{{ __('Static Page Cache') }}</Heading>
                        <Button
                            v-tooltip="__('statamic::messages.cache_utility_static_cache_description')"
                            icon="info-square"
                            icon-only
                            variant="subtle"
                            size="sm"
                            :aria-label="__('statamic::messages.cache_utility_static_cache_description')"
                            @click="$event.currentTarget.focus()"
                        >
                            <span
                                class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                                aria-hidden="true"
                            />
                        </Button>
                    </div>
                    <div v-if="static.enabled" class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Invalidate URLs'), __('Static Page Cache')]"
                            icon="live-preview"
                            :action="openInvalidateStaticUrls"
                            :when="() => !activeAction"
                        >
                            <Dropdown align="end">
                                <template #trigger>
                                    <Button
                                        :text="__('Invalidate')"
                                        size="sm"
                                        icon-append="chevron-down"
                                        :loading="activeAction === 'clearStatic' || activeAction === 'invalidateStaticUrls'"
                                        :disabled="!!activeAction"
                                    />
                                </template>
                                <DropdownMenu>
                                    <DropdownItem :text="__('Everything')" icon="layers-stacks" @click="clearStatic" />
                                    <DropdownItem :text="__('Specific URLs')" icon="link" @click="openInvalidateStaticUrls" />
                                </DropdownMenu>
                            </Dropdown>
                        </CommandPaletteItem>

                        <Modal :title="__('Invalidate Static Cache')" v-model:open="invalidateStaticUrlsModal">
                            <form class="flex flex-col gap-3" @submit.prevent="invalidateStaticUrls">
                                <Field
                                    :label="__('URLs')"
                                    :instructions="__('Specify the URLs you want to invalidate. One line per URL.')"
                                    :error="invalidateStaticUrlsError"
                                >
                                    <Textarea
                                        v-model="staticUrls"
                                        name="urls"
                                        class="font-mono"
                                        :disabled="!!activeAction"
                                    />
                                </Field>

                                <div class="flex items-center justify-end gap-3 pt-3 pb-1">
                                    <ModalClose as-child>
                                        <Button variant="ghost" :disabled="!!activeAction" :text="__('Cancel')" />
                                    </ModalClose>
                                    <Button
                                        type="submit"
                                        variant="primary"
                                        :loading="activeAction === 'invalidateStaticUrls'"
                                        :disabled="!!activeAction"
                                        :text="__('Invalidate URLs')"
                                    />
                                </div>
                            </form>
                        </Modal>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <dl v-if="static.enabled" class="grid grid-cols-1 gap-6 pb-3 @sm/panel:grid-cols-2">
                        <div class="flex min-w-0 flex-col gap-2">
                            <dt>
                                <Description>{{ __('Cached Pages') }}</Description>
                            </dt>
                            <dd>
                                <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(static.count) }}</Heading>
                            </dd>
                        </div>
                        <div class="flex min-w-0 flex-col gap-2">
                            <dt>
                                <Description>{{ __('Strategy') }}</Description>
                            </dt>
                            <dd>
                                <Heading size="2xl" class="tabular-nums">{{ static.strategy }}</Heading>
                            </dd>
                        </div>
                    </dl>
                    <div v-else class="pb-3">
                        <Badge :text="__('Disabled')" />
                    </div>
                    <Text
                        as="p"
                        :variant="results.static?.type === 'error' ? 'danger' : 'success'"
                        class="min-h-5"
                        role="status"
                    >
                        {{ results.static?.message }}
                    </Text>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex flex-wrap items-center justify-between gap-2 min-h-10">
                    <div class="flex min-w-0 items-center gap-1">
                        <Heading>{{ __('Application Cache') }}</Heading>
                        <Button
                            v-tooltip="__('statamic::messages.cache_utility_application_cache_description')"
                            icon="info-square"
                            icon-only
                            variant="subtle"
                            size="sm"
                            :aria-label="__('statamic::messages.cache_utility_application_cache_description')"
                            @click="$event.currentTarget.focus()"
                        >
                            <span
                                class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                                aria-hidden="true"
                            />
                        </Button>
                    </div>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Application Cache')]"
                            icon="live-preview"
                            :action="clearApplication"
                            :when="() => !activeAction"
                        >
                            <Button
                                :text="__('Clear')"
                                size="sm"
                                :loading="activeAction === 'clearApplication'"
                                :disabled="!!activeAction"
                                @click="clearApplication"
                            />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <dl class="pb-3">
                        <div class="flex min-w-0 flex-col gap-2">
                            <dt>
                                <Description>{{ __('Driver') }}</Description>
                            </dt>
                            <dd>
                                <Heading size="2xl" class="tabular-nums">{{ cache.driver }}</Heading>
                            </dd>
                        </div>
                    </dl>
                    <Text
                        as="p"
                        :variant="results.application?.type === 'error' ? 'danger' : 'success'"
                        class="min-h-5"
                        role="status"
                    >
                        {{ results.application?.message }}
                    </Text>
                </Card>
            </Panel>

            <Panel class="h-full flex flex-col">
                <PanelHeader class="flex flex-wrap items-center justify-between gap-2 min-h-10">
                    <div class="flex min-w-0 items-center gap-1">
                        <Heading>{{ __('Image Cache') }}</Heading>
                        <Button
                            v-tooltip="__('statamic::messages.cache_utility_image_cache_description')"
                            icon="info-square"
                            icon-only
                            variant="subtle"
                            size="sm"
                            :aria-label="__('statamic::messages.cache_utility_image_cache_description')"
                            @click="$event.currentTarget.focus()"
                        >
                            <span
                                class="absolute top-1/2 left-1/2 size-[max(100%,3rem)] -translate-1/2 pointer-fine:hidden"
                                aria-hidden="true"
                            />
                        </Button>
                    </div>
                    <div class="flex gap-2">
                        <CommandPaletteItem
                            category="Actions"
                            :text="[__('Clear'), __('Image Cache')]"
                            icon="live-preview"
                            :action="clearImage"
                            :when="() => !activeAction"
                        >
                            <Button
                                :text="__('Clear')"
                                size="sm"
                                :loading="activeAction === 'clearImage'"
                                :disabled="!!activeAction"
                                @click="clearImage"
                            />
                        </CommandPaletteItem>
                    </div>
                </PanelHeader>
                <Card class="flex-1">
                    <Deferred data="images">
                        <template #fallback>
                            <div class="grid grid-cols-1 gap-6 pb-3 @sm/panel:grid-cols-2" :aria-label="__('Loading')" aria-busy="true">
                                <div v-for="label in [__('Cached images'), __('Size')]" :key="label" class="flex flex-col gap-2">
                                    <Description>{{ label }}</Description>
                                    <Skeleton class="h-8 w-24 motion-reduce:animate-none" />
                                </div>
                            </div>
                        </template>
                        <dl class="grid grid-cols-1 gap-6 pb-3 @sm/panel:grid-cols-2">
                            <div class="flex min-w-0 flex-col gap-2">
                                <dt>
                                    <Description>{{ __('Cached images') }}</Description>
                                </dt>
                                <dd>
                                    <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(images.count) }}</Heading>
                                </dd>
                            </div>
                            <div class="flex min-w-0 flex-col gap-2">
                                <dt>
                                    <Description>{{ __('Size') }}</Description>
                                </dt>
                                <dd>
                                    <Heading size="2xl" class="tabular-nums">{{ images.size }}</Heading>
                                </dd>
                            </div>
                        </dl>
                    </Deferred>
                    <Text
                        as="p"
                        :variant="results.images?.type === 'error' ? 'danger' : 'success'"
                        class="min-h-5"
                        role="status"
                    >
                        {{ results.images?.message }}
                    </Text>
                </Card>
            </Panel>
        </div>

        <DocsCallout :topic="__('caching')" url="caching" />
    </div>
</template>
