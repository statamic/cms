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
                    <img :src="thumbnail" v-if="isImage" :title="label" />

                    <template v-else>
                        <img v-if="canShowSvg" :src="asset.url" :title="label" class="p-4" />
                        <file-icon
                            v-else
                            :extension="asset.extension"
                            class="p-4 h-full w-full"
                        />
                    </template>
                </template>

                <div class="asset-controls">
                    <div class="flex items-center justify-center space-x-1 rtl:space-x-reverse">
                        <template v-if="!readOnly">
                            <button @click="edit" class="btn btn-icon" :title="__('Edit')">
                                <svg-icon name="micro/sharp-pencil" class="h-4 my-2" />
                            </button>

                            <button @click="remove" class="btn btn-icon" :title="__('Remove')">
                                <span class="text-lg antialiased w-4">×</span>
                            </button>
                        </template>

                        <template v-else>
                            <button
                                v-if="asset.url && asset.isMedia && this.canDownload"
                                @click="open"
                                class="btn btn-icon"
                                :title="__('Open in a new window')"
                            >
                                <svg-icon name="light/external-link" class="h-4 my-2" />
                            </button>

                            <button
                                v-if="asset.allowDownloading && this.canDownload"
                                @click="download"
                                class="btn btn-icon"
                                :title="__('Download file')"
                            >
                                <svg-icon name="light/download" class="h-4 my-2" />
                            </button>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        <div class="asset-meta flex items-center" v-if="showFilename">
            <div
                class="asset-filename flex-1 px-2 py-1"
                :title="label"
                :class="{ 'text-center': !needsAlt }"
            >
                {{ label }}
            </div>
            <button
                class="asset-meta-btn"
                type="button"
                @click="edit"
                v-if="showSetAlt && needsAlt"
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
    }
};
</script>
