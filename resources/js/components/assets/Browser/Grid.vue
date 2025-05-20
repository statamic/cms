<template>
    <ui-panel v-if="!containerIsEmpty">
        <ui-panel-header class="p-1! flex items-center justify-between">
            <breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" />
            <ui-slider size="sm" class="mr-2 w-24!" variant="subtle" v-model="thumbnailSize" :min="60" :max="300" :step="25" />
        </ui-panel-header>

        <ui-card class="space-y-8">
            <!-- Folders -->
            <section class="folder-grid-listing" v-if="folders.length || creatingFolder">
                <div
                    data-folder
                    v-if="!restrictFolderNavigation"
                    v-for="folder in folders"
                    :key="folder.path"
                    class="group/folder relative"
                    :draggable="true"
                    @dragover.prevent
                    @drop="handleDropOnFolder(folder)"
                    @dragstart="draggingFolder = folder.path"
                    @dragend="draggingFolder = null"
                >
                    <Context>
                        <template #trigger>
                            <button @dblclick="selectFolder(folder.path)" class="group h-[66px] w-[80px]">
                                <ui-icon name="asset-folder" class="size-full text-blue-400/90 hover:text-blue-400" />
                                <div
                                    class="overflow-hidden text-center font-mono text-xs text-ellipsis whitespace-nowrap text-gray-500"
                                    v-text="folder.basename"
                                    :title="folder.basename"
                                />
                            </button>
                        </template>
                        <ContextMenu v-if="folderActions(folder).length">
                            <data-list-inline-actions
                                :item="folder.path"
                                :url="folderActionUrl"
                                :actions="folderActions(folder)"
                                @started="actionStarted"
                                @completed="actionCompleted"
                            />
                        </ContextMenu>
                    </Context>
                </div>
                <div v-if="creatingFolder" class="group/folder relative">
                    <div class="group h-[66px] w-[80px]">
                        <ui-icon name="asset-folder" class="size-full text-blue-400/90 hover:text-blue-400" />

                        <Editable
                            ref="newFolderInput"
                            v-model:modelValue="newFolderName"
                            :start-with-edit-mode="true"
                            submit-mode="enter"
                            :placeholder="__('New Folder')"
                            class="font-mono text-xs text-gray-500 flex items-center justify-center w-[80px] text-center overflow-hidden text-ellipsis whitespace-nowrap"
                            @submit="$emit('create-folder', newFolderName)"
                            @cancel="() => {
                                newFolderName = null;
                                $emit('cancel-creating-folder');
                            }"
                        />
                    </div>
                </div>
            </section>

            <!-- Assets -->
            <section class="asset-grid-listing" v-if="assets.length" :style="{ gridTemplateColumns: gridSize }">
                <div
                    v-for="(asset, index) in assets"
                    :key="asset.id"
                    class="group relative"
                    :class="{ selected: isSelected(asset.id) }"
                >
                    <Context>
                        <template #trigger>
                            <div class="asset-tile group relative" :class="{ 'bg-checkerboard': asset.can_be_transparent }">
                                <button
                                    data-asset
                                    class="size-full"
                                    :draggable="true"
                                    @dragover.prevent
                                    @dragstart="draggingAsset = asset.id"
                                    @dragend="draggingAsset = null"
                                    @click.stop="toggleSelection(asset.id, index, $event)"
                                    @dblclick.stop="$emit('edit-asset', asset)"
                                >
                                    <div class="relative flex aspect-square size-full items-center justify-center">
                                        <div class="asset-thumb">
                                            <img
                                                v-if="asset.is_image"
                                                :src="asset.thumbnail"
                                                loading="lazy"
                                                :draggable="false"
                                                :class="{
                                                    'size-full p-4': asset.extension === 'svg',
                                                    'rounded-lg p-1': asset.orientation === 'square',
                                                }"
                                            />
                                            <file-icon v-else :extension="asset.extension" class="size-1/2" />
                                        </div>
                                    </div>
                                </button>
                                <dropdown-list
                                    class="absolute top-1 opacity-0 group-hover:opacity-100 end-2"
                                    :class="{ 'opacity-100': actionOpened === asset.id }"
                                    @opened="actionOpened = asset.id"
                                    @closed="actionOpened = null"
                                >
                                    <dropdown-item
                                        :text="__(canEdit ? 'Edit' : 'View')"
                                        @click="edit(asset.id)"
                                    />
                                    <div class="divider" v-if="asset.actions.length" />
                                    <data-list-inline-actions
                                        :item="asset.id"
                                        :url="actionUrl"
                                        :actions="asset.actions"
                                        @started="actionStarted"
                                        @completed="actionCompleted"
                                    />
                                </dropdown-list>
                            </div>
                        </template>
                        <ContextMenu>
                            <ContextItem icon="edit" :text="__(canEdit ? 'Edit' : 'View')" @click="edit(asset.id)" />
                            <ContextSeparator />
                            <data-list-inline-actions
                                :item="asset.id"
                                :url="actionUrl"
                                :actions="asset.actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                            />
                        </ContextMenu>
                    </Context>
                    <div class="asset-filename" v-text="truncateFilename(asset.basename)" :title="asset.basename" />
                </div>
            </section>
        </ui-card>
        <ui-panel-footer>
            <slot name="footer" />
        </ui-panel-footer>
    </ui-panel>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';
import Breadcrumbs from './Breadcrumbs.vue';
import { debounce } from 'lodash-es';
import { EditableArea, EditableInput, EditablePreview, EditableRoot } from 'reka-ui';
import { Context, ContextMenu, ContextItem, ContextLabel, ContextSeparator, Editable } from '@statamic/ui';

export default {
    mixins: [AssetBrowserMixin],

    components: {
        ContextItem,
        ContextLabel,
        ContextMenu,
        ContextSeparator,
        Context,
        Editable,
        EditableInput,
        EditablePreview,
        EditableArea,
        EditableRoot,
        Breadcrumbs,
    },

    props: {
        assets: { type: Array },
        selectedAssets: { type: Array },
        creatingFolder: { type: Boolean },
    },

    data() {
        return {
            actionOpened: null,
            thumbnailSize: 200,
            newFolderName: null,
            draggingAsset: null,
            draggingFolder: null,
        };
    },

    watch: {
        thumbnailSize: {
            handler: debounce(function(size) {
                this.$preferences.set('assets.browser_thumbnail_size', size);
            }, 300)
        }
    },

    mounted() {
        const savedSize = this.$preferences.get('assets.browser_thumbnail_size');
        if (savedSize) this.thumbnailSize = savedSize;
    },

    computed: {
        gridSize() {
            return `repeat(auto-fill, minmax(${this.thumbnailSize}px, 1fr))`;
        },
    },

    methods: {
        truncateFilename(filename) {
            const maxLength = Math.floor(this.thumbnailSize / 7);
            if (filename.length <= maxLength) return filename;

            const extension = filename.split('.').pop();
            const name = filename.slice(0, -(extension.length + 1));
            const charsToKeep = Math.floor((maxLength - 3 - extension.length) / 2);

            return `${name.slice(0, charsToKeep)}…${name.slice(-charsToKeep)}.${extension}`;
        },

        isSelected(id) {
            return this.selectedAssets.includes(id);
        },

        toggleSelection(id, index, $event) {
            this.$emit('toggle-selection', id, index, $event);
        },

        focusNewFolderInput() {
            this.$refs.newFolderInput?.edit();
        },

        clearNewFolderName() {
            this.newFolderName = null;
        },

        handleDropOnFolder(destinationFolder) {
            if (this.draggingAsset) {
                let asset = this.assets.find((asset) => asset.id === this.draggingAsset);
                let action = asset.actions.find((action) => action.handle === 'move_asset');

                if (!action) {
                    return;
                }

                const payload = {
                    action: action.handle,
                    context: action.context,
                    selections: [this.draggingAsset],
                    values: { folder: destinationFolder.path },
                };

                this.$axios
                    .post(this.actionUrl, payload)
                    .then(response => this.$emit('action-completed', true, response))
                    .finally(() => this.draggingAsset = null);
            }

            if (this.draggingFolder) {
                let folder = this.folders.find((folder) => folder.path === this.draggingFolder);
                let action = folder.actions.find((action) => action.handle === 'move_asset_folder');

                if (!action) {
                    return;
                }

                const payload = {
                    action: action.handle,
                    context: action.context,
                    selections: [this.draggingFolder],
                    values: { folder: destinationFolder.path },
                };

                this.$axios
                    .post(this.folderActionUrl, payload)
                    .then(response => this.$emit('action-completed', true, response))
                    .finally(() => this.draggingFolder = null);
            }
        },
    },
};
</script>
