<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import { Button } from '@ui';
import { numberFormatter, toast } from '@api';
import AssetThumbnail from '@/components/assets/Browser/Thumbnail.vue';
import ImageCacheVariants from './ImageCacheVariants.vue';
import CacheListing from './CacheListing.vue';

const props = defineProps({
    container: {
        type: Object,
        required: true,
    },
    url: {
        type: String,
        required: true,
    },
    clearUrl: {
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
const pendingAsset = ref(null);
const selections = ref([]);
const selectedAsset = defineModel('selectedAsset', {
    type: Object,
    default: null,
});

watch(selectedAsset, () => selections.value = []);

function variantCleared(result) {
    if (result.remaining === 0) {
        selectedAsset.value = null;
    } else if (selectedAsset.value) {
        selectedAsset.value = {
            ...selectedAsset.value,
            count: result.remaining,
            size: result.remaining_size,
        };
    }

    listing.value.refresh();
    emit('cleared', result);
}

async function clear(asset, selected = false) {
    const ids = selected ? asset : [asset.id];

    if (pendingAsset.value || !ids.length || (!selected && !asset.count)) {
        return;
    }

    pendingAsset.value = selected ? 'selected' : asset.id;
    emit('processing', true);

    try {
        const { data } = await axios.post(props.clearUrl, {
            container: props.container.id,
            ...(selected ? { assets: ids } : { asset: asset.id }),
        });

        toast.success(data.message);
        selections.value = [];
        selectedAsset.value = null;
        listing.value.refresh();
        emit('cleared', data);
    } catch (error) {
        const errors = error.response?.data?.errors;

        toast.error(errors ? Object.values(errors).flat()[0] : __('Unable to clear cached images.'));
    } finally {
        pendingAsset.value = null;
        emit('processing', false);
    }
}
</script>

<template>
    <div class="flex flex-col gap-4">
        <ImageCacheVariants
            v-if="selectedAsset"
            :container="container"
            :asset="selectedAsset"
            :url="variantsUrl"
            :clear-url="clearVariantUrl"
            :processing="!!pendingAsset"
            @cleared="variantCleared"
            @processing="emit('processing', $event)"
        >
            <template #breadcrumbs>
                <slot name="breadcrumbs" />
            </template>
        </ImageCacheVariants>

        <CacheListing
            v-show="!selectedAsset"
            ref="listing"
            v-model:selections="selections"
            :url="url"
            :additional-parameters="{ container: container.id }"
            preferences-prefix="cache.image_assets"
            sort-column="size"
            sort-direction="desc"
            :allow-presets="false"
            :per-page="15"
            :show-pagination-page-links="false"
            :show-pagination-per-page-selector="false"
            :inert="pendingAsset ? true : undefined"
            :processing="!!pendingAsset"
            @bulk-action="clear($event, true)"
        >
            <template #breadcrumbs>
                <slot name="breadcrumbs" />
            </template>
            <template v-for="field in ['path', 'title']" #[`cell-${field}`]="{ row }">
                <div class="flex min-w-0 items-center gap-3">
                    <AssetThumbnail :asset="row" :square="true" class="size-8 shrink-0" />
                    <Button
                        :text="row[field]"
                        :aria-label="__('Browse cached images for :asset', { asset: row.path })"
                        variant="ghost"
                        size="sm"
                        class="-ms-3 min-w-0 shrink whitespace-normal! break-all text-start"
                        :class="{ 'font-mono': field === 'path' }"
                        :disabled="!!pendingAsset"
                        @click="selectedAsset = row"
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
                    :aria-label="__('Clear cached images for :asset', { asset: row.path })"
                    size="sm"
                    :loading="pendingAsset === row.id || (pendingAsset === 'selected' && selections.includes(row.id))"
                    :disabled="!!pendingAsset || !row.count"
                    @click="clear(row)"
                />
            </template>
        </CacheListing>
    </div>
</template>
