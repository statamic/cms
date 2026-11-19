<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { Button } from '@ui';
import { numberFormatter, toast } from '@api';
import ImageCacheAssets from './ImageCacheAssets.vue';
import ImageCacheBreadcrumbs from './ImageCacheBreadcrumbs.vue';
import CacheListing from './CacheListing.vue';

const props = defineProps({
    url: {
        type: String,
        required: true,
    },
    clearUrl: {
        type: String,
        required: true,
    },
    assetsUrl: {
        type: String,
        required: true,
    },
    clearAssetUrl: {
        type: String,
        required: true,
    },
    variantsUrl: {
        type: String,
        required: true,
    },
    clearVariantUrl: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['cleared', 'processing']);
const listing = ref(null);
const pendingContainer = ref(null);
const selectedContainer = ref(null);
const selectedAsset = ref(null);
const containerCount = ref(null);
const childProcessing = ref(false);
const selections = ref([]);

watch(selectedContainer, () => selections.value = []);

function processingChanged(processing) {
    childProcessing.value = processing;
    emit('processing', processing);
}

function showContainers() {
    selectedAsset.value = null;
    selectedContainer.value = null;
}

function containersLoaded({ response }) {
    const meta = response.data.meta;
    containerCount.value = meta.container_count;

    if (meta.single_container && !selectedContainer.value) {
        selectedContainer.value = meta.single_container;
    }
}

function assetCleared(result) {
    listing.value.refresh();
    emit('cleared', result);
}

async function clear(container, selected = false) {
    const ids = selected ? container : [container.id];

    if (pendingContainer.value || !ids.length || (!selected && !container.count)) {
        return;
    }

    pendingContainer.value = selected ? 'selected' : container.id;
    emit('processing', true);

    try {
        const { data } = await axios.post(props.clearUrl, selected ? { containers: ids } : { container: container.id });

        toast.success(data.message);
        selections.value = [];
        listing.value.refresh();
        emit('cleared', data);
    } catch (error) {
        const errors = error.response?.data?.errors;

        toast.error(errors ? Object.values(errors).flat()[0] : __('Unable to clear cached images.'));
    } finally {
        pendingContainer.value = null;
        emit('processing', false);
    }
}
</script>

<template>
    <div class="mx-auto max-w-page">
        <ImageCacheAssets
            v-if="selectedContainer"
            v-model:selected-asset="selectedAsset"
            :container="selectedContainer"
            :url="assetsUrl"
            :clear-url="clearAssetUrl"
            :variants-url="variantsUrl"
            :clear-variant-url="clearVariantUrl"
            @processing="processingChanged"
            @cleared="assetCleared"
        >
            <template #breadcrumbs>
                <ImageCacheBreadcrumbs
                    :container="selectedContainer"
                    :asset="selectedAsset"
                    :show-containers="containerCount !== 1"
                    :disabled="!!pendingContainer || childProcessing"
                    @containers="showContainers"
                    @assets="selectedAsset = null"
                />
            </template>
        </ImageCacheAssets>

        <CacheListing
            v-show="!selectedContainer"
            ref="listing"
            v-model:selections="selections"
            :url="url"
            preferences-prefix="cache.image_containers"
            sort-column="size"
            sort-direction="desc"
            :allow-presets="false"
            :per-page="15"
            :show-pagination-page-links="false"
            :show-pagination-per-page-selector="false"
            :inert="pendingContainer ? true : undefined"
            :processing="!!pendingContainer"
            @bulk-action="clear($event, true)"
            @request-completed="containersLoaded"
        >
            <template #breadcrumbs>
                <ImageCacheBreadcrumbs
                    v-if="containerCount !== null"
                    :container="selectedContainer"
                    :asset="selectedAsset"
                    :show-containers="containerCount !== 1"
                    :disabled="!!pendingContainer || childProcessing"
                    @containers="showContainers"
                    @assets="selectedAsset = null"
                />
            </template>
            <template #cell-title="{ row }">
                <Button
                    :text="row.title"
                    :aria-label="__('Browse cached images in :container', { container: row.title })"
                    variant="ghost"
                    size="sm"
                    class="-ms-3"
                    :disabled="!!pendingContainer"
                    @click="selectedContainer = row"
                />
            </template>
            <template #cell-handle="{ row }">
                <div class="font-mono">
                    <Button
                        :text="row.handle"
                        :aria-label="__('Browse cached images in :container', { container: row.title })"
                        variant="ghost"
                        size="sm"
                        class="-ms-3"
                        :disabled="!!pendingContainer"
                        @click="selectedContainer = row"
                    />
                </div>
            </template>
            <template #cell-count="{ row }">
                <div class="whitespace-nowrap tabular-nums">{{ numberFormatter.format(row.count) }}</div>
            </template>
            <template #cell-size="{ row }">
                <div class="whitespace-nowrap tabular-nums">{{ row.size_formatted }}</div>
            </template>
            <template #cell-actions="{ row }">
                <Button
                    :text="__('Clear')"
                    :aria-label="__('Clear cached images in :container', { container: row.title })"
                    size="sm"
                    :loading="pendingContainer === row.id || (pendingContainer === 'selected' && selections.includes(row.id))"
                    :disabled="!!pendingContainer || !row.count"
                    @click="clear(row)"
                />
            </template>
        </CacheListing>
    </div>
</template>
