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
                <template v-if="errors.length">
                    <div class="absolute z-10 inset-0 bg-white/75 dark:bg-dark-800/90 flex flex-col gap-2 items-center justify-center px-1 py-2">
                        <small
                            class="text-xs text-red-500 text-center"
                            v-text="errors[0]"
                        />
                    </div>
                </template>

                <!-- Solo Bard -->
                <template v-if="isImage && isInBardField && !isInAssetBrowser">
                    <img :src="asset.url" />
                </template>

                <template v-else>
                    <img :src="thumbnail" v-if="isImage" :title="label" />

                    <template v-else>
                        <img v-if="canShowSvg" :src="asset.url" :title="label" class="p-4" />
                        <file-icon v-else :extension="asset.extension" class="h-full w-full p-4" />
                    </template>
                </template>

                <div class="asset-controls z-10">
                    <div class="flex items-center justify-center space-x-1 rtl:space-x-reverse">
                        <template v-if="!readOnly">
                            <button @click="edit" class="btn btn-icon" :title="__('Edit')">
                                <svg-icon name="micro/sharp-pencil" class="my-2 h-4" />
                            </button>

                            <button @click="remove" class="btn btn-icon" :title="__('Remove')">
                                <span class="w-4 text-lg antialiased">×</span>
                            </button>
                        </template>

                        <template v-else>
                            <button
                                v-if="asset.url && asset.isMedia && this.canDownload"
                                @click="open"
                                class="btn btn-icon"
                                :title="__('Open in a new window')"
                            >
                                <svg-icon name="light/external-link" class="my-2 h-4" />
                            </button>

                            <button
                                v-if="asset.allowDownloading && this.canDownload"
                                @click="download"
                                class="btn btn-icon"
                                :title="__('Download file')"
                            >
                                <svg-icon name="light/download" class="my-2 h-4" />
                            </button>
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

export default {
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
