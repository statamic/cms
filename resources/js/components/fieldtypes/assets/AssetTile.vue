<template>
    <div
        class="asset-tile asset-thumb-container"
        :class="{
            'is-image': isImage && !canShowSvg,
            'is-svg': canShowSvg,
            'is-file': !isImage && !canShowSvg,
        }"
        :title="asset.invalid ? invalidLabel : label"
    >
        <asset-editor
            v-if="editingId"
            :id="editingId"
            :allow-deleting="false"
            :show-navigation="siblings.length > 1"
            :redirect-after-crop="false"
            @previous="navigateToPrevious"
            @next="navigateToNext"
            @closed="closeEditor"
            @saved="assetSaved"
            @created="assetCreated"
            @action-completed="actionCompleted"
        >
        </asset-editor>

        <div class="flex h-full w-full justify-center rounded-b-md relative" :class="[
            { 'border-b dark:border-gray-700': showFilename },
            canBeTransparent && checkerboardMode !== 'transparent' ? `bg-checkerboard bg-checkerboard-${checkerboardMode} rounded-lg` : '',
            canBeTransparent && checkerboardMode === 'transparent' ? `bg-checkerboard before:opacity-0 hover:before:opacity-100 rounded-lg` : '',
        ]">
            <div class="p-1 flex flex-col items-center justify-center h-full">
                <!-- Solo Bard -->
                <template v-if="isImage && isInBardField && !isInAssetBrowser">
                    <img :src="asset.url" />
                </template>

                <template v-else>
                    <img v-if="canShowSvg" :src="asset.url" :title="label" class="p-4 w-full relative" />

                    <template v-else>
                        <img :src="thumbnail" v-if="thumbnail" :title="label" class="rounded-md relative"  />

                        <file-icon v-else :extension="asset.extension ?? 'generic'" class="h-full w-full p-4 relative" />
                    </template>
                </template>

            </div>
            <div class="absolute inset-0 flex items-center justify-center opacity-0 hover:opacity-100 duration-100">
                <div class="flex items-center justify-center gap-2">
                    <template v-if="!readOnly">
                        <ui-button size="sm" @click="editOrOpen" :icon="asset.isEditable ? 'pencil' : 'eye'" aria-label="__('Edit')" v-if="asset.isViewable" />
                        <ui-button size="sm" @click="remove" icon="x" aria-label="__('Remove')" />
                    </template>

                    <template v-else>
                        <ui-button icon="external-link" size="sm" v-if="asset.url && asset.isMedia && asset.isViewable" @click="open" :aria-label="__('Open in a new window')" />
                        <ui-button icon="download" size="sm" v-if="asset.isViewable" @click="download" :aria-label="__('Download file')" />
                    </template>
                </div>
            </div>
            <div class="absolute bottom-0 end-0 [&_button]:mb-1 [&_button]:me-1">
                <ui-badge
                    v-if="!readOnly && showSetAlt && needsAlt && !showFilename"
                    as="button"
                    size="sm"
                    color="sky"
                    :text="__('Set Alt')"
                    @click="editOrOpen"
                />
            </div>
        </div>

        <div class="flex items-center justify-between w-full px-1" v-if="showFilename">
            <div
                class="truncate w-18 text-xs flex-1 px-2 py-1"
                :class="{
                    'text-center': !needsAlt,
                    'text-gray-600 dark:text-gray-300': !asset.invalid,
                    'text-gray-500 dark:text-gray-400': asset.invalid,
                }"
                v-tooltip="asset.invalid ? invalidLabel : label"
            >
                {{ label }}
            </div>
            <ui-badge as="button" size="sm" color="sky"  @click="editOrOpen" v-if="asset.isEditable && showSetAlt && needsAlt" :text="asset.values.alt ? '✅' : __('Set Alt')" />
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

    props: {
        checkerboardMode: {
            type: String,
            default: 'transparent',
        },
    },

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
