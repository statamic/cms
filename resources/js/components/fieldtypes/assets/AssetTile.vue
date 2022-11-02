<template>
    <div
        class="asset-tile"
        :class="{
            'is-image': isImage && !canShowSvg,
            'is-svg': canShowSvg,
            'is-file': !isImage && !canShowSvg,
            'col-span-2': isSolo
        }"
        :title="asset.filename"
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
                    <img :src="thumbnail" v-if="isImage" :title="label" />

                    <template v-else>
                        <img v-if="canShowSvg" :src="asset.url" class="p-2" />
                        <file-icon
                            v-else
                            :extension="asset.extension"
                            class="p-2 h-full w-full"
                        />
                    </template>
                </template>

                <div class="asset-controls" v-if="!readOnly">
                    <div class="h-full w-full flex items-center justify-center space-x-1">
                        <button
                            @click="edit"
                            class="btn btn-icon icon icon-pencil"
                            :alt="__('Edit')"
                        ></button>

                        <button
                            @click="remove"
                            class="btn btn-icon icon icon-trash"
                            :alt="__('Remove')"
                        ></button>
                    </div>
                </div>

                <div class="asset-controls" v-if="readOnly">
                    <button
                        v-if="asset.url && asset.isMedia && this.canDownload"
                        @click="open"
                        class="btn btn-icon"
                        :alt="__('Open in a new window')"
                    >
                        <svg-icon name="external-link" class="h-4 my-1" />
                    </button>

                    <button
                        v-if="asset.allowDownloading && this.canDownload"
                        @click="download"
                        class="btn btn-icon"
                        :alt="__('Download file')"
                    >
                        <svg-icon name="download" class="h-4 my-1" />
                    </button>
                </div>
            </div>
        </div>

        <div class="asset-meta flex items-center" v-if="showFilename">
            <div
                class="asset-filename flex-1 px-1 py-sm"
                :title="label"
                :class="{ 'text-center': !needsAlt }"
            >
                {{ label }}
            </div>
            <button
                class="text-blue border-l px-1 py-sm hover:bg-grey-20"
                @click="edit"
                v-if="needsAlt"
            >
                {{ asset.values.alt ? "✅" : __("Set Alt") }}
            </button>
        </div>
    </div>
</template>

<script>
import Asset from "./Asset";

export default {
    mixins: [Asset],

    props: {
        isSolo: {
            type: Boolean,
            default: false
        }
    },

    computed: {
        isInAssetBrowser() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (!parent) return false;

                if (parent.constructor.name === "AssetBrowser") {
                    return true;
                }

                vm = parent;
            }
        },

        isInBardField() {
            return this.$parent.isInBardField;
        },

        needsAlt() {
            return (this.asset.isImage || this.asset.isSvg) && !this.asset.values.alt;
        }
    }
};
</script>
