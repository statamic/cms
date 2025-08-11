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

        <div class="asset-thumb-container">
            <div class="asset-thumb" :class="{ 'bg-checkerboard': canBeTransparent }">
                <!-- Solo Bard -->
                <template v-if="isImage && isInBardField && !isInAssetBrowser">
                    <img :src="asset.url" />
                </template>

                <template v-else>
                    <img :src="thumbnail" v-if="thumbnail" :title="label" />

                    <template v-else>
                        <img v-if="canShowSvg" :src="asset.url" :title="label" class="p-4" />
                        <file-icon v-else :extension="asset.extension" class="h-full w-full p-4" />
                    </template>
                </template>

                <div class="asset-controls">
                    <div class="flex items-center justify-center space-x-1 rtl:space-x-reverse">
                        <template v-if="!readOnly">
                            <Button @click="edit" icon="edit" :title="__('Edit')" />

                            <Button @click="remove" icon="x" :title="__('Remove')" />
                        </template>

                        <template v-else>
                            <Button
                                v-if="asset.url && asset.isMedia && this.canDownload"
                                @click="open"
                                :title="__('Open in a new window')"
                                icon="external-link"
                            />

                            <Button
                                v-if="asset.allowDownloading && this.canDownload"
                                @click="download"
                                :title="__('Download file')"
                                icon="download"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="asset-meta flex items-center" v-if="showFilename">
            <div class="asset-filename flex-1 px-2 py-1" :title="label" :class="{ 'text-center': !needsAlt }">
                {{ label }}
            </div>
            <button class="asset-meta-btn" type="button" @click="edit" v-if="showSetAlt && needsAlt">
                {{ asset.values.alt ? '✅' : __('Set Alt') }}
            </button>
        </div>
    </div>
</template>

<script>
import Asset from './Asset';
import { Button } from '@statamic/ui';

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
