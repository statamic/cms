<template>

    <div class="min-h-screen">
        <div v-if="initializing" class="loading">
            <loading-graphic  />
        </div>

        <uploader
            v-if="!initializing"
            ref="uploader"
            :container="container.id"
            :path="path"
            :enabled="canUpload"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="min-h-screen">
                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-12 w-12 m-2" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div class="publish-tabs tabs rounded-none rounded-t -mx-1px shadow-none" v-if="showContainerTabs">
                    <a v-for="item in containers" :key="item.id"
                        v-text="item.title"
                        :class="{
                            active: item.id === container.id,
                            'border-b border-grey-30': item.id !== container.id
                        }"
                        @click="selectContainer(item.id)"
                    />
                </div>

                <data-list
                    v-if="!initializing"
                    :rows="assets"
                    :columns="columns"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    :sort="false"
                    :sort-column="sortColumn"
                    :sort-direction="sortDirection"
                    @selections-updated="(ids) => $emit('selections-updated', ids)"
                >
                    <div slot-scope="{ filteredRows: rows }" :class="modeClass">
                        <div class="card p-0" :class="{ 'rounded-tl-none': showContainerTabs }">
                            <div class="relative w-full">

                                <div class="data-list-header">
                                    <data-list-search v-model="searchQuery" />

                                    <template v-if="!hasSelections">
                                        <button v-if="canCreateFolders" class="btn-flat btn-icon-only ml-2" @click="creatingFolder = true">
                                            <svg-icon name="folder-add" class="h-4 w-4 mr-1" />
                                            <span>{{ __('Create Folder') }}</span>
                                        </button>

                                        <button v-if="canUpload" class="btn-flat btn-icon-only ml-2" @click="openFileBrowser">
                                            <svg-icon name="upload" class="h-4 w-4 mr-1 text-current" />
                                            <span>{{ __('Upload') }}</span>
                                        </button>
                                    </template>

                                    <div class="btn-group ml-2">
                                        <button class="btn-flat px-2" @click="setMode('grid')" :class="{'active': mode === 'grid'}">
                                            <svg-icon name="assets-mode-grid" class="h-4 w-4"/>
                                        </button>
                                        <button class="btn-flat px-2" @click="setMode('table')" :class="{'active': mode === 'table'}">
                                            <svg-icon name="assets-mode-table" class="h-4 w-4" />
                                        </button>
                                    </div>

                                </div>

                                <data-list-bulk-actions
                                    :url="bulkActionsUrl"
                                    :context="actionContext"
                                    :show-always="mode === 'grid'"
                                    @started="actionStarted"
                                    @completed="actionCompleted"
                                />
                            </div>

                            <uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                class="-mt-px"
                            />

                            <data-list-table
                                v-if="mode === 'table' && !containerIsEmpty"
                                :allow-bulk-actions="true"
                                :loading="loadingAssets"
                                :rows="rows"
                                :toggle-selection-on-row-click="true"
                                @sorted="sorted"
                            >

                                <template slot="tbody-start">
                                    <tr v-if="folder && folder.parent_path && !restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.parent_path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-8 h-8 mr-1 inline-block text-blue-lighter hover:text-blue"></file-icon>
                                                ..
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
                                    </tr>
                                    <tr v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-8 h-8 mr-1 inline-block text-blue-lighter hover:text-blue"></file-icon>
                                                {{ folder.basename }}
                                            </a>
                                        </td>
                                        <td />
                                        <td />

                                        <td class="actions-column" :colspan="columns.length">
                                            <dropdown-list v-if="folderActions(folder).length">
                                                <!-- TODO: Do we want folder edit functionality for launch? -->
                                                <!-- <dropdown-item :text="__('Edit')" @click="editedFolderPath = folder.path" /> -->

                                                <data-list-inline-actions
                                                    :item="folder.path"
                                                    :url="runFolderActionUrl"
                                                    :actions="folderActions(folder)"
                                                    @started="actionStarted"
                                                    @completed="actionCompleted"
                                                />
                                            </dropdown-list>

                                            <folder-editor
                                                v-if="editedFolderPath === folder.path"
                                                :initial-directory="folder.basename"
                                                :container="container"
                                                :path="path"
                                                @closed="editedFolderPath = null"
                                                @updated="folderUpdated($event)"
                                            />
                                        </td>
                                    </tr>
                                </template>

                                <template slot="cell-basename" slot-scope="{ row: asset, checkboxId }">
                                    <div class="flex items-center w-fit-content group">
                                        <asset-thumbnail :asset="asset" :square="true" class="w-8 h-8 mr-1 cursor-pointer" @click.native.stop="$emit('edit-asset', asset)" />
                                        <label :for="checkboxId" class="cursor-pointer select-none group-hover:text-blue" @click.stop="$emit('edit-asset', asset)">
                                            {{ asset.basename }}
                                        </label>
                                    </div>
                                </template>

                                <template slot="actions" slot-scope="{ row: asset }">
                                    <dropdown-list>
                                        <dropdown-item :text="__('Edit')" @click="edit(asset.id)" />
                                        <div class="divider" v-if="asset.actions.length" />
                                        <data-list-inline-actions
                                            :item="asset.id"
                                            :url="runActionUrl"
                                            :actions="asset.actions"
                                            @started="actionStarted"
                                            @completed="actionCompleted"
                                        />
                                    </dropdown-list>
                                </template>

                            </data-list-table>

                            <!-- Grid Mode -->
                            <div class="data-grid" v-if="mode === 'grid' && !containerIsEmpty">
                                <div class="asset-browser-grid flex flex-wrap -mx-1 px-2 pt-2">
                                    <!-- Parent Folder -->
                                    <div class="w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 mb-2 px-1 group" v-if="(folder && folder.parent_path) && !restrictFolderNavigation">
                                        <div class="w-full relative text-center cursor-pointer ratio-4:3" @click="selectFolder(folder.parent_path)">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <file-icon extension="folder" class="w-full h-full text-blue-lighter hover:text-blue"></file-icon>
                                            </div>
                                        </div>
                                        <div class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate">..</div>
                                    </div>
                                    <!-- Sub-Folders -->
                                    <div class="w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 mb-2 px-1 group" v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <div class="w-full relative text-center cursor-pointer ratio-4:3" @click="selectFolder(folder.path)">
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <file-icon extension="folder" class="w-full h-full text-blue-lighter hover:text-blue"></file-icon>
                                            </div>
                                        </div>
                                        <div class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate" v-text="folder.basename" :title="folder.basename" />
                                    </div>
                                    <!-- Assets -->
                                    <div class="w-1/3 md:w-1/4 lg:w-1/5 xl:w-1/6 mb-2 px-1 group" v-for="asset in assets" :key="asset.id">
                                        <div class="w-full relative text-center cursor-pointer ratio-4:3" @click="toggleSelection(asset.id)" @dblclick="$emit('edit-asset', asset)">
                                            <div class="absolute inset-0 flex items-center justify-center" :class="{ 'selected': isSelected(asset.id) }">
                                                <asset-thumbnail :asset="asset" class="h-full w-full" />
                                            </div>
                                        </div>
                                        <div class="text-3xs text-center text-grey-70 pt-sm w-full text-truncate" v-text="asset.basename" :title="asset.basename" />
                                    </div>
                                </div>
                            </div>

                            <div class="p-2 text-grey-70"
                                v-if="containerIsEmpty"
                                v-text="searchQuery ? __('No results') : __('This container is empty')" />

                        </div>

                        <data-list-pagination
                            class="mt-3"
                            :resource-meta="meta"
                            @page-selected="page = $event"
                        />

                    </div>
                </data-list>
            </div>
        </uploader>

        <asset-editor
            v-if="showAssetEditor"
            :id="editedAssetId"
            @closed="closeAssetEditor"
            @saved="assetSaved"
        />

        <folder-creator
            v-if="creatingFolder"
            :container="container"
            :path="path"
            @closed="creatingFolder = false"
            @created="folderCreated"
        />

    </div>

</template>

<script>
import AssetThumbnail from './Thumbnail.vue';
import AssetEditor from '../Editor/Editor.vue';
import FolderCreator from '../Folder/Create.vue';
import FolderEditor from '../Folder/Edit.vue';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';

export default {

    components: {
        AssetThumbnail,
        AssetEditor,
        Uploader,
        Uploads,
        FolderEditor,
        FolderCreator,
    },

    props: {
        // The container to display, determined by a parent component.
        // Either the ID, or the whole container object.
        initialContainer: {},
        selectedPath: String,        // The path to display, determined by a parent component.
        restrictContainerNavigation: Boolean,  // Whether to restrict to a single container and prevent navigation.
        restrictFolderNavigation: Boolean,  // Whether to restrict to a single folder and prevent navigation.
        selectedAssets: Array,
        maxFiles: Number,
        initialEditingAssetId: String,
    },

    data() {
        return {
            columns: [
                { label: __('File'), field: 'basename', visible: true },
                { label: __('Size'), field: 'size', value: 'size_formatted', visible: true },
                { label: __('Last Modified'), field: 'last_modified', value: 'last_modified_relative', visible: true },
            ],
            containers: [],
            container: {},
            initializing: true,
            loadingAssets: true,
            items: [],
            path: this.selectedPath,
            folder: {},
            searchQuery: '',
            editedAssetId: this.initialEditingAssetId,
            editedFolderPath: null,
            creatingFolder: false,
            uploads: [],
            page: 1,
            meta: {},
            sortColumn: 'basename',
            sortDirection: 'asc',
            mode: 'table',
            runActionUrl: null,
            bulkActionsUrl: null,
            runFolderActionUrl: null,
        }
    },

    computed: {
        folders() {
            return this.items.filter(item => item.itemType === 'folder')
        },

        assets() {
            return this.items.filter(item => item.itemType === 'asset')
        },

        selectedContainer() {
            return (typeof this.initialContainer === 'object')
                ? this.initialContainer.id
                : this.initialContainer;
        },

        actionContext() {
            return {container: this.selectedContainer};
        },

        showContainerTabs() {
            return !this.restrictContainerNavigation && Object.keys(this.containers).length > 1
        },

        loading() {
            return this.loadingAssets;
        },

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        canEdit() {
            return true;
            // TODO
            // return this.can('assets:'+ this.container.id +':edit')
        },

        canUpload() {
            return this.folder && this.container.allow_uploads;
        },

        canCreateFolders() {
            return this.folder && this.container.create_folders && !this.restrictFolderNavigation;
        },

        parameters() {
            return {
                page: this.page,
                sort: this.sortColumn,
                order: this.sortDirection,
                search: this.searchQuery,
                withFolders: !this.restrictFolderNavigation,
            }
        },

        hasMaxFiles() {
            return this.maxFiles !== undefined && this.maxFiles !== Infinity;
        },

        reachedSelectionLimit() {
            return this.selectedAssets.length >= this.maxFiles;
        },

        hasSelections() {
            return this.selectedAssets.length > 0;
        },

        containerIsEmpty() {
            return this.items.length === 0
                && (!this.folder || !this.folder.parent_path || this.restrictFolderNavigation);
        },

        editedAssetBasename() {
            let asset = _.find(this.assets, asset => asset.id == this.editedAssetId);

            return asset ? asset.basename : null;
        },

        modeClass() {
            return 'mode-' + this.mode;
        }

    },

    mounted() {
        this.loadContainers();
    },

    created() {
        this.$events.$on('editor-action-started', this.actionStarted);
        this.$events.$on('editor-action-completed', this.actionCompleted);
    },

    watch: {

        initialContainer() {
            this.container = this.initialContainer;
        },

        container() {
            this.loadAssets();
        },

        path() {
            this.loadAssets();
        },

        parameters(after, before) {
            if (JSON.stringify(before) === JSON.stringify(after)) return;
            this.loadAssets();
        },

        loading(loading) {
            this.$progress.loading('asset-browser', loading);
        },

        editedAssetId(editedAssetId) {
            let path = editedAssetId
                ? [this.path, this.editedAssetBasename].filter(value => value != '/').join('/') + '/edit'
                : this.path;

            this.$emit('navigated', this.container, path);
        }

    },

    methods: {

        actionStarted() {
            this.loadingAssets = true;
        },

        actionCompleted() {
            this.$toast.success(__('Action completed'));

            this.$events.$emit('clear-selections');

            this.loadAssets();
        },

        loadContainers() {
            this.$axios.get(cp_url('asset-containers')).then(response => {
                this.containers = _.chain(response.data).indexBy('id').value();
                this.container = this.containers[this.selectedContainer];
                this.mode = this.$preferences.get(`assets.${this.container.id}.mode`, this.mode);
            });
        },

        loadAssets() {
            this.loadingAssets = true;

            const url = this.searchQuery
                ? cp_url(`assets/browse/search/${this.container.id}`)
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`.trim('/'));

            this.$axios.get(url, { params: this.parameters }).then(response => {
                const data = response.data;
                this.items = data.data.items;
                this.meta = data.meta;

                if (this.searchQuery) {
                    this.folder = null;
                } else {
                    this.folder = data.data.folder;
                    this.runActionUrl = data.links.run_asset_action;
                    this.bulkActionsUrl = data.links.bulk_asset_actions;
                    this.runFolderActionUrl = data.links.run_folder_action;
                }

                this.loadingAssets = false;
                this.initializing = false;
            }).catch(e => {
                this.$toast.error(e.response.data.message, { action: null, duration: null });
                this.items = [];
                this.loadingAssets = false;
                this.initializing = false;
            });
        },

        selectFolder(path) {
            // Trigger re-loading of assets in the selected folder.
            this.path = path;
            this.selectedPage = 1;

            this.$emit('navigated', this.container, this.path);
        },

        selectContainer(id) {
            this.container = this.containers[id];
            this.path = '/';
            this.$emit('navigated', this.container, this.path);
        },

        setMode(mode) {
            this.mode = mode;
            this.$preferences.set(`assets.${this.container.id}.mode`, mode);
        },

        edit(id) {
            if (this.canEdit) {
                this.editedAssetId = id;
            }
        },

        closeAssetEditor() {
            this.editedAssetId = null;
        },

        assetSaved() {
            this.closeAssetEditor();
            this.loadAssets();
        },

        assetDeleted() {
            this.closeAssetEditor();
            this.loadAssets();
        },

        uploadsUpdated(uploads) {
            this.uploads = uploads;
        },

        uploadCompleted(asset) {
            this.loadAssets();
            this.$toast.success(__(':file uploaded', { file: asset.basename }));
        },

        uploadError(upload, uploads) {
            this.uploads = uploads;
            this.$toast.error(upload.errorMessage);
        },

        openFileBrowser() {
            this.$refs.uploader.browse();
        },

        folderCreated(folder) {
            this.creatingFolder = false;
            this.loadAssets();
        },

        folderUpdated(newFolder) {
            const index = this.items.findIndex(item =>
                item.itemType === 'folder' && item.path === this.editedFolderPath
            )
            this.items[index] = newFolder;
            this.editedFolderPath = null;
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        isSelected(id) {
            return this.selectedAssets.includes(id);
        },

        toggleSelection(id) {
            const i = this.selectedAssets.indexOf(id);

            if (i != -1) {
                this.selectedAssets.splice(i, 1);
            } else if (!this.reachedSelectionLimit) {
                this.selectedAssets.push(id);
            }
            this.$emit('selections-updated', this.selectedAssets);
        },

        folderActions(folder) {
            return folder.actions || this.folder.actions || [];
        }

    }

}
</script>
