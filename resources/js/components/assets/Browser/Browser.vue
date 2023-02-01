<template>

    <div class="min-h-screen" ref="browser" @keydown.shift="shiftDown" @keyup="clearShift">
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
                    <button class="tab-button" v-for="item in containers" :key="item.id"
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
                        <div class="card p-0" :class="{ 'rounded-tl-none': showContainerTabs, 'select-none' : shifting }">
                            <div class="relative w-full">

                                <div class="data-list-header">
                                    <data-list-search ref="search" v-model="searchQuery" />

                                    <template v-if="! hasSelections">
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

                                <breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" />

                                <data-list-bulk-actions
                                    :url="actionUrl"
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
                                v-if="mode === 'table' && ! containerIsEmpty"
                                :allow-bulk-actions="true"
                                :loading="loading"
                                :rows="rows"
                                :toggle-selection-on-row-click="true"
                                @sorted="sorted"
                            >

                                <template slot="tbody-start">
                                    <tr v-if="folder && folder.parent_path && !restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.parent_path)">
                                            <a class="flex items-center cursor-pointer group">
                                                <file-icon extension="folder" class="w-8 h-8 mr-1 inline-block text-blue-lighter group-hover:text-blue" />
                                                ..
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
                                    </tr>
                                    <tr v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.path)">
                                            <a class="flex items-center cursor-pointer group">
                                                <file-icon extension="folder" class="w-8 h-8 mr-1 inline-block text-blue-lighter group-hover:text-blue" />
                                                {{ folder.basename }}
                                            </a>
                                        </td>
                                        <td />
                                        <td />

                                        <td class="actions-column" :colspan="columns.length">
                                            <dropdown-list v-if="folderActions(folder).length">
                                                <!-- TODO: Folder edit -->
                                                <!-- <dropdown-item :text="__('Edit')" @click="editedFolderPath = folder.path" /> -->

                                                <data-list-inline-actions
                                                    :item="folder.path"
                                                    :url="folderActionUrl"
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
                                                @updated="folderUpdated(i, $event)"
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
                                        <dropdown-item :text="__(canEdit ? 'Edit' : 'View')" @click="edit(asset.id)" />
                                        <div class="divider" v-if="asset.actions.length" />
                                        <data-list-inline-actions
                                            :item="asset.id"
                                            :url="actionUrl"
                                            :actions="asset.actions"
                                            @started="actionStarted"
                                            @completed="actionCompleted"
                                        />
                                    </dropdown-list>
                                </template>

                            </data-list-table>

                            <!-- Grid Mode -->
                            <div v-if="mode === 'grid' && ! containerIsEmpty">
                                <div class="asset-grid-listing px-2 pt-1">
                                    <!-- Parent Folder -->
                                    <div class="asset-tile" v-if="(folder && folder.parent_path) && !restrictFolderNavigation">
                                        <div class="asset-thumb-container">
                                            <button @click="selectFolder(folder.parent_path)">
                                                <div class="asset-thumb">
                                                    <file-icon extension="folder" class="w-full h-full text-blue-lighter hover:text-blue" />
                                                </div>
                                            </button>
                                        </div>
                                        <div class="asset-meta flex items-center">
                                            <div class="asset-filename text-center w-full px-1 py-sm">..</div>
                                        </div>
                                    </div>
                                    <!-- Sub-Folders -->
                                    <div class="asset-tile group relative" v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <div class="asset-thumb-container">
                                            <button @click="selectFolder(folder.path)">
                                                <div class="asset-thumb">
                                                    <file-icon extension="folder" class="w-full h-full text-blue-lighter hover:text-blue" />
                                                </div>
                                            </button>
                                        </div>
                                        <div class="asset-meta flex items-center">
                                            <div class="asset-filename text-center w-full px-1 py-sm" v-text="folder.basename" :title="folder.basename" />
                                        </div>
                                        <dropdown-list autoclose v-if="folderActions(folder).length" class="absolute top-1 right-2 opacity-0 group-hover:opacity-100">
                                             <data-list-inline-actions
                                                 :item="folder.path"
                                                 :url="folderActionUrl"
                                                 :actions="folderActions(folder)"
                                                 @started="actionStarted"
                                                 @completed="actionCompleted"
                                             />
                                         </dropdown-list>
                                    </div>
                                    <!-- Assets -->
                                    <button class="asset-tile outline-none group relative" v-for="(asset, index) in assets" :key="asset.id" :class="{ 'selected': isSelected(asset.id) }" @click="toggleSelection(asset.id, index, $event)" @dblclick="$emit('edit-asset', asset)">
                                        <div class="asset-thumb-container">
                                            <div class="asset-thumb">
                                                <img v-if="asset.is_image" :src="asset.thumbnail" loading="lazy" :class="{'p-2 h-full w-full': asset.extension === 'svg'}" />
                                                <file-icon
                                                    v-else
                                                    :extension="asset.extension"
                                                    class="p-2 h-full w-full"
                                                />
                                            </div>
                                        </div>
                                        <div class="asset-meta">
                                            <div class="asset-filename px-1 py-sm text-center" v-text="asset.basename" :title="asset.basename" />
                                        </div>
                                        <dropdown-list autoclose class="absolute top-1 right-2 opacity-0 group-hover:opacity-100">
                                             <dropdown-item :text="__(canEdit ? 'Edit' : 'View')" @click="edit(asset.id)" />
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
                                </div>
                            </div>

                            <div class="p-2 text-grey-70"
                                v-if="containerIsEmpty"
                                v-text="searchQuery ? __('No results') : __('This container is empty')" />

                        </div>

                        <data-list-pagination
                            class="mt-3"
                            :resource-meta="meta"
                            :per-page="perPage"
                            @page-selected="page = $event"
                            @per-page-changed="changePerPage"
                        />

                    </div>
                </data-list>
            </div>
        </uploader>

        <asset-editor
            v-if="showAssetEditor"
            :id="editedAssetId"
            :read-only="! canEdit"
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
import Breadcrumbs from './Breadcrumbs.vue';
import FolderCreator from '../Folder/Create.vue';
import FolderEditor from '../Folder/Edit.vue';
import HasPagination from '../../data-list/HasPagination';
import HasPreferences from '../../data-list/HasPreferences';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';
import HasActions from '../../data-list/HasActions';

export default {

    mixins: [
        HasActions,
        HasPagination,
        HasPreferences,
    ],

    components: {
        AssetThumbnail,
        AssetEditor,
        Breadcrumbs,
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
        autoselectUploads: Boolean,
        autofocusSearch: Boolean,
    },

    data() {
        return {
            columns: [
                { label: __('File'), field: 'basename', visible: true, sortable: true },
                { label: __('Size'), field: 'size', value: 'size_formatted', visible: true, sortable: true },
                { label: __('Last Modified'), field: 'last_modified', value: 'last_modified_relative', visible: true, sortable: true },
            ],
            containers: [],
            container: {},
            initializing: true,
            loading: true,
            assets: [],
            path: this.selectedPath,
            folders: [],
            folder: {},
            searchQuery: '',
            editedAssetId: this.initialEditingAssetId,
            editedFolderPath: null,
            creatingFolder: false,
            uploads: [],
            page: 1,
            preferencesPrefix: null,
            meta: {},
            sortColumn: this.initialContainer.sort_field,
            sortDirection: this.initialContainer.sort_direction,
            mode: 'table',
            actionUrl: null,
            folderActionUrl: null,
            shifting: false,
            lastItemClicked: null
        }
    },

    computed: {

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

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        canEdit() {
            return this.can('edit '+ this.container.id +' assets')
        },

        canUpload() {
            return this.folder && this.container.allow_uploads;
        },

        canCreateFolders() {
            return this.folder && this.container.create_folders && ! this.restrictFolderNavigation;
        },

        parameters() {
            return {
                page: this.page,
                perPage: this.perPage,
                sort: this.sortColumn,
                order: this.sortDirection,
                search: this.searchQuery,
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
            return this.assets.length === 0
                && this.folders.length === 0
                && (!this.folder || !this.folder.parent_path);
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

        container(container) {
            this.preferencesPrefix = `assets.${container.id}`;
            this.mode = this.getPreference('mode') || 'table';
            this.setInitialPerPage();
            this.loadAssets();
        },

        path() {
            this.loadAssets();
        },

        parameters(after, before) {
            if (JSON.stringify(before) === JSON.stringify(after)) return;
            this.loadAssets();
        },

        initializing(isInitializing, wasInitializing) {
            if (wasInitializing && this.autofocusSearch) {
                this.$nextTick(() => this.$refs.search.focus());
            }
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

        afterActionSuccessfullyCompleted() {
            this.loadAssets();
        },

        loadContainers() {
            this.$axios.get(cp_url('asset-containers')).then(response => {
                this.containers = _.chain(response.data).indexBy('id').value();
                this.container = this.containers[this.selectedContainer];
            });
        },

        loadAssets() {
            this.loading = true;

            const url = this.searchQuery
                ? cp_url(`assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ''}`).replace(/\/$/, '')
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`).replace(/\/$/, '');

            this.$axios.get(url, { params: this.parameters }).then(response => {
                const data = response.data;
                this.assets = data.data.assets;
                this.meta = data.meta;

                if (this.searchQuery) {
                    this.folder = null;
                    this.folders = [];
                } else {
                    this.folder = data.data.folder;
                    this.folders = data.data.folder.folders;
                    this.actionUrl = data.links.asset_action;
                    this.folderActionUrl = data.links.folder_action;
                }

                this.loading = false;
                this.initializing = false;
            }).catch(e => {
                this.$toast.error(e.response.data.message, { action: null, duration: null });
                this.assets = [];
                this.folders = [];
                this.loading = false;
                this.initializing = false;
            });
        },

        selectFolder(path) {
            // Trigger re-loading of assets in the selected folder.
            this.path = path;
            this.page = 1;

            this.$emit('navigated', this.container, this.path);
        },

        selectContainer(id) {
            this.container = this.containers[id];
            this.path = '/';
            this.page = 1;

            this.$emit('navigated', this.container, this.path);
        },

        setMode(mode) {
            this.mode = mode;
            this.setPreference('mode', mode == 'table' ? null : mode);
        },

        edit(id) {
            this.editedAssetId = id;
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
            if (this.autoselectUploads) {
                this.sortColumn = 'last_modified';
                this.sortDirection = 'desc';

                this.selectedAssets.push(asset.id);
                this.$emit('selections-updated', this.selectedAssets);
            }

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
            this.folders.push(folder);
            this.folders = _.sortBy(this.folders, 'title');
            this.creatingFolder = false;
        },

        folderUpdated(index, newFolder) {
            this.folders[index] = newFolder;
            this.editedFolderPath = null;
        },

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
        },

        isSelected(id) {
            return this.selectedAssets.includes(id);
        },

        toggleSelection(id, index, $event) {
            const i = this.selectedAssets.indexOf(id);
            this.$refs.browser.focus()

            if (i != -1) {
                this.selectedAssets.splice(i, 1);
            } else if (! this.reachedSelectionLimit) {
                if ($event.shiftKey && this.lastItemClicked !== null) {
                    this.selectRange(
                        Math.min(this.lastItemClicked, index),
                        Math.max(this.lastItemClicked, index)
                    );
                } else {
                    this.selectedAssets.push(id);
                }
            }
            this.$emit('selections-updated', this.selectedAssets);
            this.lastItemClicked = index;
        },

        folderActions(folder) {
            return folder.actions || this.folder.actions || [];
        },

        selectRange(from, to) {
            for (var i = from; i <= to; i++ ) {
                let asset = this.assets[i].id;
                if (! this.selectedAssets.includes(asset) && ! this.reachedSelectionLimit) {
                    this.selectedAssets.push(asset);
                }
                this.$emit('selections-updated', this.selectedAssets);
            };
        },

        shiftDown() {
            this.shifting = true
        },

        clearShift() {
            this.shifting = false
        },

    }

}
</script>
