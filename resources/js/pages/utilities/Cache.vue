<script setup>
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { config, numberFormatter, toast } from '@api';
import Head from '@/pages/layout/Head.vue';
import StaticCacheExplorer from '@/components/utilities/StaticCacheExplorer.vue';
import ImageCacheExplorer from '@/components/utilities/ImageCacheExplorer.vue';
import {
    Header, Button, Panel, PanelHeader, Heading, Card, Description, Badge, Text, Skeleton, Icon,
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
    'cacheStaticPageUrl',
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

const linkToDocs = config.get('linkToDocs');

const cacheSections = computed(() => {
    const sections = [
        {
            id: 'static',
            title: __('Static Page Cache'),
            help: __('statamic::messages.cache_utility_static_cache_description'),
            documentation: 'https://statamic.dev/static-caching',
            enabled: props.static.enabled,
            metrics: [
                {
                    label: __('Cached pages'),
                    value: numberFormatter.format(props.static.count),
                },
                {
                    label: __('Strategy'),
                    value: props.static.strategy,
                },
            ],
            actions: [
                {
                    label: __('Warm'),
                    handle: 'warmStatic',
                    run: warmStatic,
                },
                {
                    label: __('Clear URLs...'),
                    handle: 'invalidateStaticUrls',
                    run: openInvalidateStaticUrls,
                },
                {
                    label: __('Clear'),
                    handle: 'clearStatic',
                    run: clearStatic,
                },
            ],
        },
        {
            id: 'images',
            title: __('Image Cache'),
            help: __('statamic::messages.cache_utility_image_cache_description'),
            documentation: 'https://statamic.dev/image-manipulation#caching',
            enabled: true,
            loading: !props.images,
            metrics: [
                {
                    label: __('Variations'),
                    value: props.images ? numberFormatter.format(props.images.count) : null,
                },
                {
                    label: __('Assets'),
                    value: props.images ? numberFormatter.format(props.images.assets) : null,
                },
                {
                    label: __('Size'),
                    value: props.images?.size,
                },
            ],
            actions: [
                {
                    label: __('Clear'),
                    handle: 'clearImage',
                    run: clearImage,
                },
            ],
        },
        {
            id: 'stache',
            title: __('Content Stache'),
            help: __('statamic::messages.cache_utility_stache_description'),
            documentation: 'https://statamic.dev/stache',
            enabled: true,
            metrics: [
                {
                    label: __('Records'),
                    value: numberFormatter.format(props.stache.records),
                },
                {
                    label: __('Size'),
                    value: props.stache.size,
                },
                {
                    label: __('Build time'),
                    value: props.stache.time,
                    badge: true,
                },
                {
                    label: __('Last rebuild'),
                    value: props.stache.rebuilt,
                    badge: true,
                },
            ].filter((metric) => metric.value),
            actions: [
                {
                    label: __('Warm'),
                    handle: 'warmStache',
                    run: warmStache,
                },
                {
                    label: __('Clear'),
                    handle: 'clearStache',
                    run: clearStache,
                },
            ],
        },
        {
            id: 'application',
            title: __('Application Cache'),
            help: __('statamic::messages.cache_utility_application_cache_description'),
            documentation: 'https://statamic.dev/caching#application-cache',
            enabled: true,
            metrics: [
                {
                    label: __('Driver'),
                    value: props.cache.driver,
                },
            ],
            actions: [
                {
                    label: __('Clear'),
                    handle: 'clearApplication',
                    run: clearApplication,
                },
            ],
        },
    ];
    const order = props.static.enabled
        ? ['static', 'stache', 'images', 'application']
        : ['stache', 'images', 'application', 'static'];

    return order.map((id) => sections.find((section) => section.id === id));
});
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

function clearStache() {
    runAction('clearStache', props.clearStacheUrl);
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

    <Modal :title="__('Clear URLs')" v-model:open="invalidateStaticUrlsModal">
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
                :instructions="__('statamic::messages.cache_utility_urls_instructions')"
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
                    :text="__('Clear')"
                />
            </div>
        </form>
    </Modal>

    <Stack
        v-model:open="explorerOpen"
        :title="__('Static Page Cache')"
        :before-close="() => !activeAction"
        size="full"
    >
        <StaticCacheExplorer
            v-if="explorerOpen"
            v-model:status="explorerStatus"
            :url="staticPagesUrl"
            :cache-url="cacheStaticPageUrl"
            :invalidate-url="invalidatePagesUrl"
            :sites="cacheSites"
            @processing="activeAction = $event ? 'staticPageAction' : null"
            @invalidated="cachedPagesInvalidated"
            @cached="cachedPagesInvalidated"
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

        <div class="flex flex-col gap-4">
            <Panel v-for="section in cacheSections" :key="section.id" class="mb-0!">
                <PanelHeader class="flex min-h-10 flex-wrap items-center justify-between gap-x-4 gap-y-3">
                    <div class="flex min-w-0 items-center gap-1">
                        <Heading :level="2">{{ section.title }}</Heading>
                        <Button
                            v-tooltip="section.help"
                            :aria-label="section.help"
                            icon="info-square"
                            icon-only
                            variant="subtle"
                            size="sm"
                            @click="$event.currentTarget.focus()"
                        />
                        <Badge v-if="!section.enabled" :text="__('Disabled')" />
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <template v-if="section.enabled">
                            <Dropdown v-if="section.id === 'static'" align="end">
                                <template #trigger>
                                    <Button
                                        :text="__('Browse pages')"
                                        icon-append="chevron-down"
                                        size="sm"
                                        :disabled="!!activeAction"
                                    />
                                </template>
                                <DropdownMenu>
                                    <DropdownItem :text="__('Cached pages')" @click="browseStaticPages('cached')" />
                                    <DropdownItem :text="__('Uncached pages')" @click="browseStaticPages('uncached')" />
                                </DropdownMenu>
                            </Dropdown>
                            <Button
                                v-else-if="section.id === 'images'"
                                :text="__('Browse images')"
                                :aria-label="__('Browse cached images')"
                                icon-append="chevron-right"
                                size="sm"
                                :disabled="!!activeAction"
                                @click="imageExplorerOpen = true"
                            />
                            <Button
                                v-for="action in section.actions"
                                :key="action.handle"
                                :text="action.label"
                                size="sm"
                                :loading="activeAction === action.handle"
                                :disabled="!!activeAction"
                                @click="action.run()"
                            />
                        </template>
                    </div>
                </PanelHeader>
                <Card v-if="section.enabled" inset>
                    <div class="flex flex-col gap-3 px-4.5 py-4">
                        <dl class="flex flex-wrap gap-x-10 gap-y-4">
                            <div
                                v-for="metric in section.metrics.filter((metric) => !metric.badge)"
                                :key="metric.label"
                                class="flex flex-col gap-1"
                            >
                                <dt>
                                    <Description>{{ metric.label }}</Description>
                                </dt>
                                <dd>
                                    <Skeleton v-if="section.loading" class="h-8 w-20" />
                                    <Heading v-else size="2xl" class="tabular-nums">{{ metric.value }}</Heading>
                                </dd>
                            </div>
                        </dl>
                        <div v-if="section.metrics.some((metric) => metric.badge)" class="flex flex-wrap gap-2">
                            <Badge
                                v-for="metric in section.metrics.filter((metric) => metric.badge)"
                                :key="metric.label"
                                :prepend="metric.label"
                                class="tabular-nums"
                            >
                                {{ metric.value }}
                            </Badge>
                        </div>
                        <Description v-if="section.id === 'static' && section.enabled && staticCoverage" class="tabular-nums">
                            {{ __n('statamic::messages.cache_utility_coverage', staticCoverage.total, {
                                cached: numberFormatter.format(staticCoverage.cached),
                                total: numberFormatter.format(staticCoverage.total),
                            }) }}
                        </Description>
                    </div>
                </Card>
            </Panel>
        </div>

        <div v-if="linkToDocs" class="mt-12 mb-10 flex justify-center text-center">
            <Dropdown align="center" side="top">
                <template #trigger>
                    <Badge
                        as="button"
                        type="button"
                        :text="__('Learn about caching')"
                        icon-append="chevron-down"
                        pill
                    />
                </template>
                <DropdownMenu>
                    <DropdownItem
                        v-for="section in cacheSections"
                        :key="section.id"
                        :href="section.documentation"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <span>{{ section.title }}</span>
                            <Icon name="external-link" class="size-3! text-gray-500" aria-hidden="true" />
                        </div>
                    </DropdownItem>
                </DropdownMenu>
            </Dropdown>
        </div>
    </div>
</template>
