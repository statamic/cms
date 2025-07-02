<template>
    <ui-card class="space-y-8">
        <!-- Folders -->
        <section class="folder-grid-listing" v-if="folders.length || creatingFolder">
            <div
                v-if="!restrictFolderNavigation"
                v-for="folder in folders"
                :key="folder.path"
                class="group/folder relative p-1"
                :class="{ 'rounded-xl ring-2 ring-blue-400': dragOverFolder === folder.path }"
                :draggable="true"
                @dragover.prevent="dragOverFolder = folder.path"
                @dragleave.prevent="dragOverFolder = null"
                @drop="
                    handleFolderDrop(folder);
                    dragOverFolder = null;
                "
                @dragstart="draggingFolder = folder.path"
                @dragend="
                    draggingFolder = null;
                    dragOverFolder = null;
                "
            >
                <ItemActions
                    :url="folderActionUrl"
                    :actions="folderActions(folder)"
                    :item="folder.path"
                    @started="actionStarted"
                    @completed="actionCompleted"
                    v-slot="{ actions }"
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
                        <ContextMenu>
                            <ContextItem
                                v-for="action in actions"
                                :key="action.handle"
                                :text="__(action.title)"
                                :icon="action.icon"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run"
                            />
                        </ContextMenu>
                    </Context>
                </ItemActions>
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
                        class="flex w-[80px] items-center justify-center overflow-hidden text-center font-mono text-xs text-ellipsis whitespace-nowrap text-gray-500"
                        @submit="$emit('create-folder', newFolderName)"
                        @cancel="
                            () => {
                                newFolderName = null;
                                $emit('cancel-creating-folder');
                            }
                        "
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
                <ItemActions
                    :url="actionUrl"
                    :actions="asset.actions"
                    :item="asset.id"
                    @started="actionStarted"
                    @completed="actionCompleted"
                    v-slot="{ actions }"
                >
                    <Context>
                        <template #trigger>
                            <div
                                class="asset-tile group relative bg-white"
                                :class="{
                                    'bg-checkerboard!': asset.can_be_transparent,
                                    'opacity-50!': draggingAsset === asset.id,
                                }"
                            >
                                <button
                                    class="size-full"
                                    :draggable="true"
                                    @dragover.prevent
                                    @dragstart="draggingAsset = asset.id"
                                    @dragend="draggingAsset = null"
                                    @click.stop="selectionClicked(index, $event)"
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
                                <div class="absolute end-2 top-1">
                                    <Dropdown placement="left-start">
                                        <DropdownMenu>
                                            <DropdownLabel :text="__('Actions')" />
                                            <DropdownItem
                                                :text="__(canEdit ? 'Edit' : 'View')"
                                                @click="edit(asset.id)"
                                                icon="edit"
                                            />
                                            <DropdownSeparator v-if="asset.actions.length" />
                                            <DropdownItem
                                                v-for="action in actions"
                                                :key="action.handle"
                                                :text="__(action.title)"
                                                :icon="action.icon"
                                                :variant="action.dangerous ? 'destructive' : 'default'"
                                                @click="action.run"
                                            />
                                        </DropdownMenu>
                                    </Dropdown>
                                </div>
                            </div>
                        </template>
                        <ContextMenu>
                            <ContextLabel :text="__('Actions')" />
                            <ContextItem icon="edit" :text="__(canEdit ? 'Edit' : 'View')" @click="edit(asset.id)" />
                            <ContextSeparator />
                            <ContextItem
                                v-for="action in actions"
                                :key="action.handle"
                                :text="__(action.title)"
                                :icon="action.icon"
                                :variant="action.dangerous ? 'destructive' : 'default'"
                                @click="action.run"
                            />
                        </ContextMenu>
                    </Context>
                </ItemActions>
                <div class="asset-filename" v-text="truncateFilename(asset.basename)" :title="asset.basename" />
            </div>
        </section>
    </ui-card>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';
import Breadcrumbs from './Breadcrumbs.vue';
import {
    Context,
    ContextMenu,
    ContextItem,
    ContextLabel,
    ContextSeparator,
    Editable,
    Dropdown,
    DropdownMenu,
    DropdownLabel,
    DropdownItem,
    DropdownSeparator,
} from '@statamic/ui';
import { injectListingContext } from '@statamic/components/ui/Listing/Listing.vue';
import ItemActions from '@statamic/components/actions/ItemActions.vue';

export default {
    mixins: [AssetBrowserMixin],

    components: {
        Breadcrumbs,
        ContextItem,
        ContextLabel,
        ContextMenu,
        ContextSeparator,
        Context,
        Editable,
        Dropdown,
        DropdownMenu,
        DropdownLabel,
        DropdownItem,
        DropdownSeparator,
        ItemActions,
    },

    props: {
        assets: { type: Array },
        selectedAssets: { type: Array },
        thumbnailSize: { type: Number },
    },

    data() {
        return {
            dragOverFolder: null,
            shifting: false,
        };
    },

    computed: {
        gridSize() {
            return `repeat(auto-fill, minmax(${this.thumbnailSize}px, 1fr))`;
        },
    },

    setup() {
        const { selectionClicked } = injectListingContext();

        return { selectionClicked };
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
    },
};
</script>
