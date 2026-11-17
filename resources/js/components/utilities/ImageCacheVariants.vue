<script setup>
import { computed, ref } from 'vue';
import axios from 'axios';
import { Button } from '@ui';
import { dateFormatter, toast } from '@api';
import FileIcon from '@/components/FileIcon.vue';
import CacheListing from './CacheListing.vue';

const props = defineProps({
    container: {
        type: Object,
        required: true,
    },
    asset: {
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
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['cleared', 'processing']);
const listing = ref(null);
const dimensions = ref({});
const failed = ref(new Set());
const pendingVariant = ref(null);
const selections = ref([]);
const busy = computed(() => props.processing || pendingVariant.value !== null);

async function clearVariant(variant, selected = false) {
    const ids = selected ? variant : [variant.id];

    if (busy.value || !ids.length) {
        return;
    }

    pendingVariant.value = selected ? 'selected' : variant.id;
    emit('processing', true);

    try {
        const { data } = await axios.post(props.clearUrl, {
            container: props.container.id,
            asset: props.asset.id,
            ...(selected ? { variants: ids } : { variant: variant.id }),
        });

        toast.success(data.message);
        ids.forEach((id) => {
            delete dimensions.value[id];
            failed.value.delete(id);
        });
        selections.value = [];

        if (data.remaining > 0) {
            listing.value.refresh();
        }

        emit('cleared', data);
    } catch (error) {
        const errors = error.response?.data?.errors;

        toast.error(errors ? Object.values(errors).flat()[0] : __('Unable to clear cached variation.'));
    } finally {
        pendingVariant.value = null;
        emit('processing', false);
    }
}

function imageLoaded(row, event) {
    const { naturalWidth, naturalHeight } = event.target;

    if (naturalWidth && naturalHeight) {
        dimensions.value[row.id] = `${naturalWidth} × ${naturalHeight}`;
    }
}

function generatedDate(date) {
    return dateFormatter.format(date, {
        preset: 'datetime',
        second: '2-digit',
        timeZoneName: 'short',
    });
}
</script>

<template>
    <CacheListing
        ref="listing"
        v-model:selections="selections"
        :url="url"
        :additional-parameters="{ container: container.id, asset: asset.id }"
        preferences-prefix="cache.image_variants"
        sort-column="size"
        sort-direction="desc"
        :allow-presets="false"
        :allow-customizing-columns="false"
        :per-page="15"
        :show-pagination-page-links="false"
        :show-pagination-per-page-selector="false"
        :inert="busy || undefined"
        :processing="busy"
        @bulk-action="clearVariant($event, true)"
    >
        <template #breadcrumbs>
            <slot name="breadcrumbs" />
        </template>
        <template #cell-preview="{ row }">
            <a
                v-if="!failed.has(row.id)"
                :href="row.preview_url"
                :aria-label="__('View cached image :filename', { filename: row.title })"
                target="_blank"
                rel="noopener noreferrer"
            >
                <img
                    :src="row.preview_url"
                    alt=""
                    width="96"
                    height="64"
                    loading="lazy"
                    class="h-16 w-24 rounded-sm object-contain outline-1 -outline-offset-1 outline-black/5 dark:outline-white/10"
                    @load="imageLoaded(row, $event)"
                    @error="failed.add(row.id)"
                />
            </a>
            <div v-else class="flex h-16 w-24 items-center justify-center">
                <FileIcon :extension="row.format.toLowerCase()" class="size-8" />
            </div>
        </template>
        <template #cell-dimensions="{ row, isColumnVisible }">
            <img
                v-if="!isColumnVisible('preview') && !failed.has(row.id)"
                :src="row.preview_url"
                alt=""
                loading="lazy"
                class="sr-only"
                @load="imageLoaded(row, $event)"
                @error="failed.add(row.id)"
            />
            <div class="whitespace-nowrap tabular-nums">{{ dimensions[row.id] || '—' }}</div>
        </template>
        <template #cell-size="{ row }">
            <div class="whitespace-nowrap tabular-nums">{{ row.size_formatted }}</div>
        </template>
        <template #cell-generated_at="{ row }">
            <time
                v-tooltip="generatedDate(row.generated_at)"
                :datetime="row.generated_at"
                :title="generatedDate(row.generated_at)"
                class="whitespace-nowrap tabular-nums"
            >
                {{ dateFormatter.format(row.generated_at, { relative: true }) }}
            </time>
        </template>
        <template #cell-hash="{ row }">
            <div class="font-mono text-[0.8125rem]">{{ row.hash }}</div>
        </template>
        <template #cell-actions="{ row }">
            <Button
                :text="__('Clear')"
                :aria-label="__('Clear cached variation :hash', { hash: row.hash })"
                size="sm"
                :loading="pendingVariant === row.id || (pendingVariant === 'selected' && selections.includes(row.id))"
                :disabled="busy"
                @click="clearVariant(row)"
            />
        </template>
    </CacheListing>
</template>
