<template>
    <div class="dark:bg-dark-800 h-full bg-white">
        <div class="flex h-full min-h-0 flex-col">
            <div class="flex flex-1 flex-col gap-4 overflow-scroll p-4">
                <AssetBrowser
                    :container="container"
                    :initial-per-page="$config.get('paginationSize')"
                    :initial-columns="columns"
                    :selected-path="folder"
                    :selected-assets="browserSelections"
                    :restrict-folder-navigation="restrictFolderNavigation"
                    :max-files="maxFiles"
                    :query-scopes="queryScopes"
                    :autoselect-uploads="true"
                    allow-selecting-existing-upload
                    :allow-bulk-actions="false"
                    @selections-updated="selectionsUpdated"
                    @asset-doubleclicked="select"
                    @initialized="focusSearchInput"
                >
                    <template #initializing>
                        <div class="flex flex-1">
                            <div class="absolute inset-0 z-200 flex items-center justify-center text-center">
                                <loading-graphic />
                            </div>
                        </div>
                    </template>

                    <template #header="{ canUpload, openFileBrowser, canCreateFolders, startCreatingFolder, mode, modeChanged }">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="flex flex-1 items-center gap-3">
                                <Search ref="search" />
                            </div>

                            <Button v-if="canUpload" :text="__('Upload')" icon="upload" @click="openFileBrowser" />
                            <Button v-if="canCreateFolders" :text="__('Create Folder')" icon="folder-add" @click="startCreatingFolder" />

                            <ToggleGroup :model-value="mode" @update:model-value="modeChanged">
                                <ToggleItem icon="layout-grid" value="grid" />
                                <ToggleItem icon="layout-list" value="table" />
                            </ToggleGroup>
                        </div>
                    </template>
                </AssetBrowser>
            </div>

            <div class="flex items-center justify-between border-t bg-gray-100 p-4">
                <div
                    class="dark:text-dark-150 text-sm text-gray-700"
                    v-text="
                        hasMaxFiles
                            ? __n(':count/:max selected', browserSelections, { max: maxFiles })
                            : __n(':count asset selected|:count assets selected', browserSelections)
                    "
                />

                <div class="flex items-center space-x-3">
                    <Button variant="ghost" @click="close">
                        {{ __('Cancel') }}
                    </Button>

                    <Button variant="primary" @click="select">
                        {{ __('Select') }}
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import AssetBrowser from './Browser/Browser.vue'
import {
    Button,
    ToggleGroup,
    ToggleItem,
    ListingTable as Table,
    ListingSearch as Search,
    ListingPagination as Pagination,
    Panel,
    PanelFooter,
    ListingPagination, Slider, PanelHeader,
} from '@statamic/ui';
import HasPreferences from '@statamic/components/data-list/HasPreferences.js';
import Breadcrumbs from '@statamic/components/assets/Browser/Breadcrumbs.vue';
import Grid from '@statamic/components/assets/Browser/Grid.vue';
import Uploads from '@statamic/components/assets/Uploads.vue';

export default {
    mixins: [HasPreferences],

    components: {
        Uploads,
        PanelHeader, Grid,
        Slider,
        ListingPagination, Breadcrumbs,
        AssetBrowser,
        Button,
        ToggleGroup,
        ToggleItem,
        Table,
        Search,
        Pagination,
        Panel,
        PanelFooter,
    },

    props: {
        container: Object,
        folder: String,
        selected: Array,
        maxFiles: Number,
        queryScopes: Array,
        columns: Array,
        restrictFolderNavigation: {
            type: Boolean,
            default() {
                return false;
            },
        },
    },

    data() {
        return {
            // We will initialize the browser component with the selections, but not pass in the selections directly.
            // We only want selection changes to be reflected in the fieldtype once the user is ready to commit
            // them. They should be able to cancel at any time and have their updated selections discarded.
            browserSelections: this.selected,
        };
    },

    computed: {
        hasMaxFiles() {
            return this.maxFiles === Infinity ? false : Boolean(this.maxFiles);
        },
    },

    watch: {
        browserSelections(selections) {
            if (this.maxFiles === 1 && selections.length === 1) {
                this.select();
            }
        },
    },

    methods: {
        /**
         * Confirm the updated selections
         */
        select() {
            this.$emit('selected', this.browserSelections);
            this.close();
        },

        /**
         * Close this selector
         */
        close() {
            this.$emit('closed');
        },

        /**
         * Selections have been updated within the browser component.
         */
        selectionsUpdated(selections) {
            this.browserSelections = selections;
        },

        focusSearchInput() {
            this.$nextTick(() => this.$refs.search.focus());
        },
    },
};
</script>
