<script setup>
import { computed, ref, watch } from 'vue';
import axios from 'axios';
import { Button, Field, Select } from '@ui';
import { dateFormatter, toast } from '@api';
import CacheListing from './CacheListing.vue';

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
    invalidateUrl: {
        type: String,
        required: true,
    },
    cacheUrl: {
        type: String,
        required: true,
    },
    sites: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['invalidated', 'cached', 'processing']);
const status = defineModel('status', {
    type: String,
    default: 'cached',
});
const listing = ref(null);
const site = ref(null);
const domain = ref(null);
const domains = ref([]);
const selections = ref([]);
const processing = ref(false);
const pendingAction = ref(null);

const siteOptions = computed(() => [
    { value: null, label: __('All sites') },
    ...props.sites,
]);
const domainOptions = computed(() => {
    const values = [...new Set([...domains.value, ...(domain.value ? [domain.value] : [])])];

    return [
        { value: null, label: __('All domains') },
        ...values.map((value) => ({ value, label: value })),
    ];
});

watch([site, domain, status], () => {
    selections.value = [];
});

function requestCompleted({ response }) {
    domains.value = response.data.meta.domains;
}

function generatedDate(date) {
    return dateFormatter.format(date, {
        preset: 'datetime',
        second: '2-digit',
        timeZoneName: 'short',
    });
}

async function cachePages(urls, selected = false) {
    if (status.value !== 'uncached' || processing.value || !urls.length) {
        return;
    }

    processing.value = true;
    emit('processing', true);
    const cached = [];
    const failed = [];

    try {
        for (const url of urls) {
            pendingAction.value = url;

            try {
                const { data } = await axios.post(props.cacheUrl, { url });

                cached.push({
                    url,
                    data,
                });
            } catch (error) {
                const errors = error.response?.data?.errors;

                failed.push(errors
                    ? Object.values(errors).flat()[0]
                    : error.response?.data?.message || __('Unable to cache this page.'));
            }
        }

        if (cached.length) {
            const cachedUrls = cached.map((page) => page.url);
            selections.value = selections.value.filter((url) => !cachedUrls.includes(url));

            toast.success(selected
                ? __n('statamic::messages.cache_utility_pages_cached', cached.length)
                : cached[0].data.message);
            listing.value.refresh();

            const result = selected
                ? {
                    count: cached.length,
                    urls: cachedUrls,
                }
                : cached[0].data;

            emit('cached', result);
        }

        if (failed.length) {
            toast.error(selected
                ? __n('statamic::messages.cache_utility_pages_cache_failed', failed.length)
                : failed[0]);
        }
    } finally {
        processing.value = false;
        pendingAction.value = null;
        emit('processing', false);
    }
}

async function invalidate(urls, selected = false) {
    if (status.value !== 'cached' || processing.value || !urls.length) {
        return;
    }

    processing.value = true;
    pendingAction.value = selected ? 'selected' : urls[0];
    emit('processing', true);

    try {
        const { data } = await axios.post(props.invalidateUrl, {
            urls,
            site: site.value,
            domain: domain.value,
        });

        toast.success(data.message);
        selections.value = [];
        listing.value.refresh();
        emit('invalidated', data);
    } catch (error) {
        const errors = error.response?.data?.errors;

        toast.error(errors ? Object.values(errors).flat()[0] : __('Unable to clear cached pages.'));
    } finally {
        processing.value = false;
        pendingAction.value = null;
        emit('processing', false);
    }
}
</script>

<template>
    <div class="mx-auto flex max-w-page flex-col gap-4">
        <div class="flex flex-wrap items-end gap-4">
            <Field :label="__('Status')" class="min-w-40 flex-1">
                <Select
                    v-model="status"
                    :options="[
                        { value: 'cached', label: __('Cached') },
                        { value: 'uncached', label: __('Uncached') },
                    ]"
                    :disabled="processing"
                />
            </Field>
            <Field :label="__('Site')" class="min-w-48 flex-1">
                <Select v-model="site" :options="siteOptions" :placeholder="__('All sites')" :disabled="processing" />
            </Field>
            <Field :label="__('Domain')" class="min-w-48 flex-1">
                <Select v-model="domain" :options="domainOptions" :placeholder="__('All domains')" :disabled="processing" />
            </Field>
        </div>

        <CacheListing
            :key="status"
            ref="listing"
            v-model:selections="selections"
            :url="url"
            :additional-parameters="{ site, domain, status }"
            :preferences-prefix="status === 'cached' ? 'cache.static_pages' : 'cache.uncached_pages'"
            :sort-column="status === 'cached' ? 'generated_at' : 'url'"
            :sort-direction="status === 'cached' ? 'desc' : 'asc'"
            allow-selections
            :allow-presets="false"
            :allow-select-all-matching="false"
            :per-page="15"
            :max-selections="1000"
            :show-pagination-page-links="false"
            :show-pagination-per-page-selector="false"
            :inert="processing || undefined"
            :bulk-action="status === 'cached' ? 'Clear' : 'Cache'"
            :processing="processing"
            @bulk-action="status === 'cached' ? invalidate($event, true) : cachePages($event, true)"
            @update:search-query="selections = []"
            @request-completed="requestCompleted"
        >
            <template #cell-url="{ row }">
                <div class="text-[0.8125rem]">
                    <a :href="row.url" target="_blank" rel="noopener noreferrer" class="break-all font-mono">
                        {{ row.url }}
                    </a>
                </div>
            </template>
            <template #cell-path="{ row }">
                <div class="text-[0.8125rem]">
                    <a :href="row.url" target="_blank" rel="noopener noreferrer" class="break-all font-mono">
                        {{ row.path }}
                    </a>
                </div>
            </template>
            <template #cell-generated_at="{ row }">
                <time
                    v-if="row.generated_at"
                    v-tooltip="generatedDate(row.generated_at)"
                    :datetime="row.generated_at"
                    :title="generatedDate(row.generated_at)"
                    class="whitespace-nowrap tabular-nums"
                >
                    {{ dateFormatter.format(row.generated_at, { relative: true }) }}
                </time>
                <span v-else>—</span>
            </template>
            <template #cell-site_name="{ row }">
                {{ row.site_name || __('Unknown site') }}
            </template>
            <template #cell-actions="{ row }">
                <Button
                    v-if="status === 'cached'"
                    :text="__('Clear')"
                    :aria-label="__('Clear :url', { url: row.url })"
                    size="sm"
                    :loading="pendingAction === row.url"
                    :disabled="processing"
                    @click="invalidate([row.url])"
                />
                <Button
                    v-else
                    :text="__('Cache')"
                    :aria-label="__('Cache :url', { url: row.url })"
                    size="sm"
                    :loading="pendingAction === row.url"
                    :disabled="processing"
                    @click="cachePages([row.url])"
                />
            </template>
        </CacheListing>
    </div>
</template>
