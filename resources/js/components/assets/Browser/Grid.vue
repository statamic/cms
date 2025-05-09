<template>
    <ui-panel v-if="!containerIsEmpty">
        <ui-panel-header class="p-1! flex items-center justify-between">
            <breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" />
            <ui-slider size="sm" class="mr-2 w-24!" variant="subtle" v-model="thumbnailSize" :min="60" :max="300" :step="25" />
        </ui-panel-header>
        <!-- Folders -->
        <ui-card class="space-y-8">
            <section class="folder-grid-listing" v-if="folders.length">
                <div
                    class="group/folder relative"
                    v-for="folder in folders"
                    :key="folder.path"
                    v-if="!restrictFolderNavigation"
                >
                    <button @click="selectFolder(folder.path)" class="w-[80px] h-[66px] group">
                        <ui-icon name="asset-folder" class="size-full text-blue-400/90 hover:text-blue-400" />
                        <div
                            class="font-mono text-xs text-gray-500 text-center overflow-hidden text-ellipsis whitespace-nowrap"
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
            <section class="asset-grid-listing"
                v-if="assets.length"
                :style="{ gridTemplateColumns: gridSize }"
            >
                <div
                    v-for="(asset, index) in assets"
                    :key="asset.id"
                    class="group relative"
                    :class="{ 'selected': isSelected(asset.id) }"
                >
                    <div class="asset-tile group relative" :class="{ 'bg-checkerboard': asset.can_be_transparent }">
                        <button
                            class="size-full"
                            @click.stop="toggleSelection(asset.id, index, $event)"
                            @dblclick.stop="$emit('edit-asset', asset)"
                        >
                            <div class="relative flex items-center justify-center aspect-square size-full">
                                <div class="asset-thumb">
                                    <img
                                        v-if="asset.is_image"
                                        :src="asset.thumbnail"
                                        loading="lazy"
                                        :class="{
                                            'size-full p-4': asset.extension === 'svg',
                                            'p-1 rounded-lg': asset.orientation === 'square'
                                        }"
                                    />
                                    <file-icon v-else :extension="asset.extension" class="size-full p-4" />
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
                    <div
                        class="asset-filename"
                        v-text="truncateFilename(asset.basename)"
                        :title="asset.basename"
                    />
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

export default {
    mixins: [AssetBrowserMixin],
    components: { Breadcrumbs },
    props: {
        assets: { type: Array },
        selectedAssets: { type: Array },
    },

    data() {
        return {
            actionOpened: null,
            thumbnailSize: 200,
        };
    },

    watch: {
        thumbnailSize: {
            handler: debounce(function(size) {
                this.$preferences.set('asset-browser-thumbnail-size', size);
            }, 300)
        }
    },

    mounted() {
        const savedSize = this.$preferences.get('asset-browser-thumbnail-size');
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
    },
};
</script>
