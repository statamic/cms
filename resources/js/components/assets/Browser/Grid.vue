<template>
    <div v-if="!containerIsEmpty">
        <!-- Folders -->
        <section class="flex flex-wrap gap-8">
            <!-- Parent Folder -->
            <div v-if="folder && folder.parent_path && !restrictFolderNavigation">
                <button @click="selectFolder(folder.parent_path)" class="w-[80px] h-[66px]">
                    <ui-icon name="asset-folder" class="h-full w-full text-blue-400" />
                    <div
                        class="font-mono text-xs text-gray-500 text-start overflow-hidden text-ellipsis w-24 whitespace-nowrap"
                    >../</div>
                </button>
            </div>
            <!-- Sub-Folders -->
            <div
                class="group/folder relative"
                v-for="(folder, i) in folders"
                :key="folder.path"
                v-if="!restrictFolderNavigation"
            >
                <button @click="selectFolder(folder.path)" class="w-[80px] h-[66px]">
                    <ui-icon name="asset-folder" class="h-full w-full text-blue-400" />
                    <div
                        class="font-mono text-xs text-gray-500 text-start overflow-hidden text-ellipsis w-24 whitespace-nowrap"
                        v-text="folder.basename"
                        :title="folder.basename"
                    />
                </button>
                <dropdown-list
                    v-if="folderActions(folder).length"
                    class="absolute top-1 opacity-0 group-hover:opacity-100 end-2"
                    :class="{ 'opacity-100': actionOpened === folder.path }"
                    @opened="actionOpened = folder.path"
                    @closed="actionOpened = null"
                >
                    <data-list-inline-actions
                        :item="folder.path"
                        :url="folderActionUrl"
                        :actions="folderActions(folder)"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                </dropdown-list>
            </div>
        </section>

        <!-- Assets -->
        <section class="flex flex-wrap gap-2">
            <button
                class="asset-tile group relative outline-hidden"
                v-for="(asset, index) in assets"
                :key="asset.id"
                :class="{ selected: isSelected(asset.id) }"
            >
                <div
                    class="w-full"
                    @click.stop="toggleSelection(asset.id, index, $event)"
                    @dblclick.stop="$emit('edit-asset', asset)"
                >
                    <div class="asset-thumb-container">
                        <div
                            class="asset-thumb"
                            :class="{ 'bg-checkerboard': asset.can_be_transparent }"
                        >
                            <img
                                v-if="asset.is_image"
                                :src="asset.thumbnail"
                                loading="lazy"
                                :class="{ 'h-full w-full p-4': asset.extension === 'svg' }"
                            />
                            <file-icon
                                v-else
                                :extension="asset.extension"
                                class="h-full w-full p-4"
                            />
                        </div>
                    </div>
                    <div class="asset-meta">
                        <div
                            class="asset-filename px-2 py-1 text-center"
                            v-text="asset.basename"
                            :title="asset.basename"
                        />
                    </div>
                </div>
                <dropdown-list
                    class="absolute top-1 opacity-0 group-hover:opacity-100 ltr:right-2 rtl:left-2"
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
            </button>
        </section>
    </div>
</template>

<script>
import AssetBrowserMixin from './AssetBrowserMixin';

export default {
    mixins: [AssetBrowserMixin],

    props: {
        assets: Array,
        selectedAssets: Array,
    },

    data() {
        return {
            actionOpened: null,
        };
    },

    methods: {

        isSelected(id) {
            return this.selectedAssets.includes(id);
        },

        toggleSelection(id, index, $event) {
            this.$emit('toggle-selection', { id, index, event: $event });
        },
    },
};
</script>
