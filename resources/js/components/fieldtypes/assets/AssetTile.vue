<template>
    <div
        class="asset-tile"
        :class="{
            'is-image': isImage && !canShowSvg,
            'is-svg': canShowSvg,
            'is-file': !isImage && !canShowSvg,
        }"
        :title="label"
    >
        <asset-editor
            v-if="editing"
            :id="asset.id"
            :allow-deleting="false"
            @closed="closeEditor"
            @saved="assetSaved"
            @action-completed="actionCompleted"
        >
        </asset-editor>

        <div class="flex h-full border-b rounded-b-md relative">
            <div class="p-1 flex flex-col items-center justify-center h-full" :class="{ 'bg-checkerboard': canBeTransparent }">
                <!-- Solo Bard -->
                <template v-if="isImage && isInBardField && !isInAssetBrowser">
                    <img :src="asset.url" />
                </template>

                <template v-else>
                    <img :src="thumbnail" v-if="thumbnail" :title="label" class="rounded-md"  />

                    <template v-else>
                        <img v-if="canShowSvg" :src="asset.url" :title="label" class="p-4" />
                        <file-icon v-else :extension="asset.extension" class="h-full w-full p-4" />
                    </template>
                </template>
            </div>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 duration-100">
                <div class="flex items-center justify-center gap-2">
                    <template v-if="!readOnly">
                        <ui-button size="sm" @click="edit" icon="pencil" aria-label="__('Edit')" />
                        <ui-button size="sm" @click="remove" icon="x" aria-label="__('Remove')" />
                    </template>

                    <template v-else>
                        <ui-button icon="external-link" size="sm" v-if="asset.url && asset.isMedia && this.canDownload" @click="open" :aria-label="__('Open in a new window')" />
                        <ui-button icon="download" size="sm" v-if="asset.allowDownloading && this.canDownload" @click="download" :aria-label="__('Download file')" />
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between w-full px-1" v-if="showFilename">
            <div class="truncate w-18 text-xs text-gray-500 flex-1 px-2 py-1" v-tooltip="label" :class="{ 'text-center': !needsAlt }">
                {{ label }}
            </div>
            <ui-badge as="button" size="sm" color="blue" variant="flat" @click="edit" v-if="showSetAlt && needsAlt" :text="asset.values.alt ? '✅' : __('Set Alt')" />
        </div>
    </div>
</template>

<script>
import Asset from './Asset';
import { Button } from '@/components/ui';

export default {
    components: {
        Button,
    },

    mixins: [Asset],

    computed: {
        isInAssetBrowser() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (!parent) return false;

                if (parent.constructor.name === 'AssetBrowser') {
                    return true;
                }

                vm = parent;
            }
        },

        isInBardField() {
            return this.$parent.isInBardField;
        },
    },
};
</script>
