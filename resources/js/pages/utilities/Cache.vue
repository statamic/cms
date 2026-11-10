<script setup>
import { Deferred, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import axios from 'axios';
import { numberFormatter, toast } from '@api';
import Head from '@/pages/layout/Head.vue';
import StaticCacheExplorer from '@/components/utilities/StaticCacheExplorer.vue';
import ImageCacheExplorer from '@/components/utilities/ImageCacheExplorer.vue';
import {
    Header, Button, Panel, PanelHeader, PanelFooter, Heading, Card, Description, Badge, Text, Skeleton,
    CommandPaletteItem, Dropdown, DropdownMenu, DropdownItem, Textarea,
    Field, Modal, ModalClose, Select, Stack, StackContent,
} from '@ui';

const props = defineProps([
    'stache',
    'cache',
    'static',
    'staticCoverage',
    'images',
    'cacheSites',
    'staticPagesUrl',
    'previewStaticPagesUrl',
    'imageContainersUrl',
    'clearImageContainerUrl',
    'imageAssetsUrl',
    'clearImageAssetUrl',
    'imageVariantsUrl',
    'clearImageVariantUrl',
    'clearAllUrl',
    'clearStacheUrl',
    'warmStacheUrl',
    'clearStaticUrl',
    'warmStaticUrl',
    'invalidatePagesUrl',
    'clearApplicationUrl',
    'clearImageUrl',
]);

const activeAction = ref(null);
const staticUrls = ref(null);
const invalidateStaticUrlsModal = ref(false);
const invalidateStaticUrlsError = ref(null);
const explorerOpen = ref(false);
const explorerStatus = ref('cached');
const imageExplorerOpen = ref(false);
const invalidationSite = ref(null);
const preview = ref(null);
const previewError = ref(null);
const previewLoading = ref(false);

watch([staticUrls, invalidationSite, invalidateStaticUrlsModal], ([urls, site, open], previous, onCleanup) => {
    preview.value = null;
    previewError.value = null;
    previewLoading.value = false;
    invalidateStaticUrlsError.value = null;

    const rules = urls?.split('\n').map((url) => url.trim()).filter(Boolean) ?? [];

    if (!open || !rules.length) {
        return;
    }

    const controller = new AbortController();
    previewLoading.value = true;

    const timeout = setTimeout(async () => {
        try {
            const { data } = await axios.post(props.previewStaticPagesUrl, { urls: rules, site }, {
                signal: controller.signal,
            });

            if (!controller.signal.aborted) {
                preview.value = data;
            }
        } catch (error) {
            if (controller.signal.aborted) {
                return;
            }

            const errors = error.response?.data?.errors;
            previewError.value = errors ? Object.values(errors).flat()[0] : __('Unable to preview cached pages.');
        } finally {
            if (!controller.signal.aborted) {
                previewLoading.value = false;
            }
        }
    }, 300);

    onCleanup(() => {
        clearTimeout(timeout);
        controller.abort();
    });
});

function cachedPagesInvalidated() {
    router.reload({ only: ['static', 'staticCoverage'], preserveScroll: true });
}

function browseStaticPages(status = 'cached') {
    explorerStatus.value = status;
    explorerOpen.value = true;
}

function cachedImagesCleared() {
    router.reload({ only: ['images'], preserveScroll: true });
}

function runAction(action, url, options = {}) {
    if (activeAction.value) {
        return;
    }

    activeAction.value = action;

    router.post(url, options.data ?? {}, {
        preserveScroll: true,
        onSuccess: () => options.onSuccess?.(),
        onError: (errors) => {
            if (options.onError) {
                options.onError(errors);
            } else {
                toast.error(Object.values(errors).flat()[0]);
            }
        },
        onFinish: () => {
            activeAction.value = null;
        },
    });
}

function clearAll() {
    runAction('clearAll', props.clearAllUrl);
}

function rebuildStache() {
    runAction('rebuildStache', props.clearStacheUrl);
}

function warmStache() {
    runAction('warmStache', props.warmStacheUrl);
}

function clearStatic() {
    runAction('clearStatic', props.clearStaticUrl);
}

function warmStatic() {
    runAction('warmStatic', props.warmStaticUrl);
}

function clearApplication() {
    runAction('clearApplication', props.clearApplicationUrl);
}

function clearImage() {
    runAction('clearImage', props.clearImageUrl);
}

function openInvalidateStaticUrls() {
    if (activeAction.value) {
        return;
    }

    invalidateStaticUrlsError.value = null;
    invalidateStaticUrlsModal.value = true;
}

function invalidateStaticUrls() {
    if (previewLoading.value || !preview.value?.total) {
        return;
    }

    invalidateStaticUrlsError.value = null;

    runAction('invalidateStaticUrls', props.invalidatePagesUrl, {
        data: {
            urls: staticUrls.value?.split('\n'),
            site: invalidationSite.value,
        },
        onSuccess: () => {
            staticUrls.value = null;
            invalidateStaticUrlsModal.value = false;
        },
        onError: (errors) => {
            invalidateStaticUrlsError.value = Object.values(errors).flat()[0];
        },
    });
}
</script>

<template>
    <Head :title="[__('Cache Manager'), __('Utilities')]" />

    <Stack
        v-model:open="explorerOpen"
        :title="__('Cached pages')"
        :before-close="() => !activeAction"
        size="full"
    >
        <StaticCacheExplorer
            v-if="explorerOpen"
            v-model:status="explorerStatus"
            :url="staticPagesUrl"
            :invalidate-url="invalidatePagesUrl"
            :sites="cacheSites"
            @processing="activeAction = $event ? 'invalidateStaticUrls' : null"
            @invalidated="cachedPagesInvalidated"
        />
    </Stack>

    <Stack
        v-model:open="imageExplorerOpen"
        :title="__('Cached images')"
        :before-close="() => !activeAction"
        size="full"
    >
        <StackContent class="pt-0!">
            <ImageCacheExplorer
                v-if="imageExplorerOpen"
                :url="imageContainersUrl"
                :clear-url="clearImageContainerUrl"
                :assets-url="imageAssetsUrl"
                :clear-asset-url="clearImageAssetUrl"
                :variants-url="imageVariantsUrl"
                :clear-variant-url="clearImageVariantUrl"
                @processing="activeAction = $event ? 'clearImageContainer' : null"
                @cleared="cachedImagesCleared"
            />
        </StackContent>
    </Stack>

    <div class="max-w-page mx-auto">
        <Header :title="__('Cache Manager')" icon="cache">
            <CommandPaletteItem
                category="Actions"
                :text="__('Clear all caches')"
                icon="live-preview"
                :action="clearAll"
                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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


        <div class="@container/cache-grid">
            <div class="grid auto-rows-fr grid-cols-1 gap-4 @4xl/cache-grid:grid-cols-2">
                <Panel class="mb-0! grid grid-rows-[auto_1fr_auto]">
                    <PanelHeader class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 min-h-10">
                        <div class="flex min-w-0 items-center gap-1">
                            <Heading :level="2">{{ __('Content Stache') }}</Heading>
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
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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
                    <Card inset>
                        <div class="flex flex-col gap-3 px-4.5 py-4">
                            <dl class="flex flex-wrap gap-x-10 gap-y-4">
                                <div class="flex min-w-0 flex-col gap-1">
                                    <dt>
                                        <Description>{{ __('Records') }}</Description>
                                    </dt>
                                    <dd>
                                        <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(stache.records) }}</Heading>
                                    </dd>
                                </div>
                                <div v-if="stache.size" class="flex min-w-0 flex-col gap-1">
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
                        </div>
                    </Card>
                    <PanelFooter class="row-start-3 flex pt-1! pb-0!">
                        <Button
                            href="https://statamic.dev/stache"
                            target="_blank"
                            rel="noopener noreferrer"
                            :text="__('Documentation')"
                            icon-append="external-link"
                            variant="subtle"
                            size="sm"
                            class="-ms-3"
                        />
                    </PanelFooter>
                </Panel>

                <Panel class="mb-0! grid grid-rows-[auto_1fr_auto]">
                    <PanelHeader class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 min-h-10">
                        <div class="flex min-w-0 items-center gap-1">
                            <Heading :level="2">{{ __('Static Page Cache') }}</Heading>
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
                                :text="[__('Warm'), __('Static Page Cache')]"
                                icon="fire-flame-burn-hot"
                                :action="warmStatic"
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
                            >
                                <Button
                                    :text="__('Warm')"
                                    variant="ghost"
                                    size="sm"
                                    :loading="activeAction === 'warmStatic'"
                                    :disabled="!!activeAction"
                                    @click="warmStatic"
                                />
                            </CommandPaletteItem>
                            <Dropdown align="end">
                                <template #trigger>
                                    <Button
                                        :text="__('Browse')"
                                        :aria-label="__('Browse pages')"
                                        size="sm"
                                        icon-append="chevron-down"
                                        :disabled="!!activeAction"
                                    />
                                </template>
                                <DropdownMenu>
                                    <DropdownItem :text="__('Cached pages')" @click="browseStaticPages('cached')" />
                                    <DropdownItem :text="__('Uncached pages')" @click="browseStaticPages('uncached')" />
                                </DropdownMenu>
                            </Dropdown>
                            <CommandPaletteItem
                                category="Actions"
                                :text="[__('Invalidate URLs'), __('Static Page Cache')]"
                                icon="live-preview"
                                :action="openInvalidateStaticUrls"
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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
                                    <Field v-if="cacheSites.length > 1" :label="__('Site')">
                                        <Select
                                            v-model="invalidationSite"
                                            :options="[{ value: null, label: __('All sites') }, ...cacheSites]"
                                            :placeholder="__('All sites')"
                                            :disabled="!!activeAction"
                                        />
                                    </Field>
                                    <Field
                                        :label="__('URLs')"
                                        :instructions="__('One URL per line. Use /blog/* to include a section.')"
                                        :error="invalidateStaticUrlsError || previewError"
                                    >
                                        <Textarea
                                            v-model="staticUrls"
                                            name="urls"
                                            autocomplete="off"
                                            autocapitalize="off"
                                            :spellcheck="false"
                                            :placeholder="'/about\n/blog/*'"
                                            :disabled="!!activeAction"
                                        />
                                    </Field>

                                    <Description v-if="previewLoading" role="status">
                                        {{ __('Loading preview...') }}
                                    </Description>
                                    <div v-else-if="preview" class="flex flex-col gap-2" aria-live="polite">
                                        <Text as="p">
                                            {{ __n('statamic::messages.cache_utility_matches', preview.total) }}
                                        </Text>
                                        <ul
                                            v-if="preview.total"
                                            role="list"
                                            class="flex max-h-48 flex-col gap-1 overflow-auto rounded-lg bg-gray-50 p-3 dark:bg-gray-900"
                                        >
                                            <li v-for="page in preview.data" :key="page.url" class="break-all font-mono text-sm">
                                                {{ page.url }}
                                            </li>
                                        </ul>
                                        <Description v-if="preview.total > preview.data.length">
                                            {{ __('Showing :count of :total matches.', { count: preview.data.length, total: preview.total }) }}
                                        </Description>
                                    </div>

                                    <div class="flex items-center justify-end gap-3 pt-3 pb-1">
                                        <ModalClose as-child>
                                            <Button variant="ghost" :disabled="!!activeAction" :text="__('Cancel')" />
                                        </ModalClose>
                                        <Button
                                            type="submit"
                                            variant="primary"
                                            :loading="activeAction === 'invalidateStaticUrls'"
                                            :disabled="!!activeAction || previewLoading || !preview?.total"
                                            :text="__('Invalidate URLs')"
                                        />
                                    </div>
                                </form>
                            </Modal>
                        </div>
                        <Badge v-else :text="__('Disabled')" />
                    </PanelHeader>
                    <Card v-if="static.enabled" inset>
                        <div class="flex flex-col gap-3 px-4.5 py-4">
                            <dl v-if="static.enabled" class="flex flex-wrap gap-x-10 gap-y-4">
                                <div class="flex min-w-0 flex-col gap-1">
                                    <dt>
                                        <Description>{{ __('Cached Pages') }}</Description>
                                    </dt>
                                    <dd>
                                        <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(static.count) }}</Heading>
                                    </dd>
                                </div>
                                <div class="flex min-w-0 flex-col gap-1">
                                    <dt>
                                        <Description>{{ __('Strategy') }}</Description>
                                    </dt>
                                    <dd>
                                        <Heading size="2xl" class="tabular-nums">{{ static.strategy }}</Heading>
                                    </dd>
                                </div>
                            </dl>
                            <Deferred data="staticCoverage">
                                <template #fallback>
                                    <Skeleton class="h-5 w-64 motion-reduce:animate-none" :aria-label="__('Loading')" />
                                </template>
                                <Description v-if="staticCoverage" class="tabular-nums">
                                    {{ __n('statamic::messages.cache_utility_coverage', staticCoverage.total, {
                                        cached: numberFormatter.format(staticCoverage.cached),
                                        total: numberFormatter.format(staticCoverage.total),
                                    }) }}
                                </Description>
                            </Deferred>
                        </div>
                    </Card>
                    <PanelFooter class="row-start-3 flex pt-1! pb-0!">
                        <Button
                            href="https://statamic.dev/static-caching"
                            target="_blank"
                            rel="noopener noreferrer"
                            :text="static.enabled ? __('Documentation') : __('Set up static caching')"
                            icon-append="external-link"
                            variant="subtle"
                            size="sm"
                            class="-ms-3"
                        />
                    </PanelFooter>
                </Panel>

                <Panel class="mb-0! grid grid-rows-[auto_1fr_auto]">
                    <PanelHeader class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 min-h-10">
                        <div class="flex min-w-0 items-center gap-1">
                            <Heading :level="2">{{ __('Application Cache') }}</Heading>
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
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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
                    <Card inset>
                        <div class="flex flex-col gap-3 px-4.5 py-4">
                            <dl>
                                <div class="flex min-w-0 flex-col gap-1">
                                    <dt>
                                        <Description>{{ __('Driver') }}</Description>
                                    </dt>
                                    <dd>
                                        <Heading size="2xl" class="tabular-nums">{{ cache.driver }}</Heading>
                                    </dd>
                                </div>
                            </dl>
                        </div>
                    </Card>
                    <PanelFooter class="row-start-3 flex pt-1! pb-0!">
                        <Button
                            href="https://statamic.dev/caching#application-cache"
                            target="_blank"
                            rel="noopener noreferrer"
                            :text="__('Documentation')"
                            icon-append="external-link"
                            variant="subtle"
                            size="sm"
                            class="-ms-3"
                        />
                    </PanelFooter>
                </Panel>

                <Panel class="mb-0! grid grid-rows-[auto_1fr_auto]">
                    <PanelHeader class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2 min-h-10">
                        <div class="flex min-w-0 items-center gap-1">
                            <Heading :level="2">{{ __('Image Cache') }}</Heading>
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
                            <Button
                                :text="__('Browse')"
                                :aria-label="__('Browse cached images')"
                                size="sm"
                                :disabled="!!activeAction"
                                @click="imageExplorerOpen = true"
                            />
                            <CommandPaletteItem
                                category="Actions"
                                :text="[__('Clear'), __('Image Cache')]"
                                icon="live-preview"
                                :action="clearImage"
                                :when="() => !activeAction && !explorerOpen && !imageExplorerOpen"
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
                    <Card inset>
                        <div class="flex flex-col gap-3 px-4.5 py-4">
                            <Deferred data="images">
                                <template #fallback>
                                    <div class="flex flex-wrap gap-x-10 gap-y-4" :aria-label="__('Loading')" aria-busy="true">
                                        <div v-for="label in [__('Variations'), __('Assets'), __('Size')]" :key="label" class="flex flex-col gap-1">
                                            <Description>{{ label }}</Description>
                                            <Skeleton class="h-8 w-24 motion-reduce:animate-none" />
                                        </div>
                                    </div>
                                </template>
                                <dl class="flex flex-wrap gap-x-10 gap-y-4">
                                    <div class="flex min-w-0 flex-col gap-1">
                                        <dt>
                                            <Description>{{ __('Variations') }}</Description>
                                        </dt>
                                        <dd>
                                            <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(images.count) }}</Heading>
                                        </dd>
                                    </div>
                                    <div class="flex min-w-0 flex-col gap-1">
                                        <dt>
                                            <Description>{{ __('Assets') }}</Description>
                                        </dt>
                                        <dd>
                                            <Heading size="2xl" class="tabular-nums">{{ numberFormatter.format(images.assets) }}</Heading>
                                        </dd>
                                    </div>
                                    <div class="flex min-w-0 flex-col gap-1">
                                        <dt>
                                            <Description>{{ __('Size') }}</Description>
                                        </dt>
                                        <dd>
                                            <Heading size="2xl" class="tabular-nums">{{ images.size }}</Heading>
                                        </dd>
                                    </div>
                                </dl>
                            </Deferred>
                        </div>
                    </Card>
                    <PanelFooter class="row-start-3 flex pt-1! pb-0!">
                        <Button
                            href="https://statamic.dev/image-manipulation#caching"
                            target="_blank"
                            rel="noopener noreferrer"
                            :text="__('Documentation')"
                            icon-append="external-link"
                            variant="subtle"
                            size="sm"
                            class="-ms-3"
                        />
                    </PanelFooter>
                </Panel>
            </div>
        </div>
    </div>
</template>
