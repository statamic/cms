<template>
    <div class="min-h-screen" ref="browser" @keydown.shift="shiftDown" @keyup="clearShift">
        <div v-if="initializing" class="loading">
            <loading-graphic />
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
            v-slot="{ dragging }"
        >
            <div class="min-h-screen">
                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="m-4 size-12" />
                    <span>{{ __('Drop File to Upload') }}</span>
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
                    v-slot="{ filteredRows: rows }"
                >
                    <div :class="modeClass">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <data-list-search ref="search" v-model="searchQuery" />
                                <!-- <data-list-filters /> -->
                            </div>


                                <!-- <button
                                    v-if="canCreateFolders"
                                    class="btn btn-sm ltr:ml-3 rtl:mr-3"
                                    @click="creatingFolder = true"
                                >
                                    <svg-icon name="folder-add" class="h-4 w-4 ltr:mr-2 rtl:ml-2" />
                                    <span>{{ __('Create Folder') }}</span>
                                </button> -->

                                <!-- <button
                                    v-if="canUpload"
                                    class="btn btn-sm ltr:ml-3 rtl:mr-3"
                                    @click="openFileBrowser"
                                >
                                    <svg-icon name="upload" class="h-4 w-4 text-current ltr:mr-2 rtl:ml-2" />
                                    <span>{{ __('Upload') }}</span>
                                </button> -->

                                <!-- <div class="btn-group ltr:ml-3 rtl:mr-3">
                                    <button
                                        class="btn btn-sm"
                                        @click="setMode('grid')"
                                        :class="{ active: mode === 'grid' }"
                                    >
                                        <svg-icon name="assets-mode-grid" class="h-4 w-4" />
                                    </button>
                                    <button
                                        class="btn btn-sm"
                                        @click="setMode('table')"
                                        :class="{ active: mode === 'table' }"
                                    >
                                        <svg-icon name="assets-mode-table" class="h-4 w-4" />
                                    </button>
                                </div> -->
                            <!-- <breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" /> -->

                            <uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                :allow-selecting-existing="allowSelectingExistingUpload"
                                :class="{ '-mt-px': !hasSelections, 'mt-10': hasSelections }"
                                @existing-selected="existingUploadSelected"
                            />

                            <Table
                                v-if="mode === 'table'"
                                :mode="mode"
                                :container-is-empty="containerIsEmpty"
                                :loading="loading"
                                :folder="folder"
                                :folders="folders"
                                :columns="columns"
                                :restrict-folder-navigation="restrictFolderNavigation"
                                :folder-action-url="folderActionUrl"
                                :action-url="actionUrl"
                                :can-edit="canEdit"
                                @select-folder="selectFolder"
                                @edit="edit"
                                @sorted="sorted"
                                @action-started="actionStarted"
                                @action-completed="actionCompleted"
                                @edit-asset="$emit('edit-asset', $event)"
                            />

                            <!-- Grid Mode -->
                            <Grid
                                v-if="mode === 'grid'"
                                :container-is-empty="containerIsEmpty"
                                :folder="folder"
                                :folders="folders"
                                :assets="assets"
                                :restrict-folder-navigation="restrictFolderNavigation"
                                :folder-action-url="folderActionUrl"
                                :action-url="actionUrl"
                                :can-edit="canEdit"
                                :selected-assets="selectedAssets"
                                @select-folder="selectFolder"
                                @toggle-selection="toggleSelection"
                                @edit="edit"
                                @action-started="actionStarted"
                                @action-completed="actionCompleted"
                                @edit-asset="$emit('edit-asset', $event)"
                            />

                            <div
                                class="p-4 text-gray-700"
                                v-if="containerIsEmpty"
                                v-text="searchQuery ? __('No results') : __('This container is empty')"
                            />
                        </div>

                        <data-list-pagination
                            class="mt-6"
                            :resource-meta="meta"
                            :per-page="perPage"
                            @page-selected="page = $event"
                            @per-page-changed="changePerPage"
                        />
                    </div>

                    <data-list-bulk-actions
                        :url="actionUrl"
                        :context="actionContext"
                        :show-always="mode === 'grid'"
                        @started="actionStarted"
                        @completed="actionCompleted"
                    />
                </data-list>
            </div>
        </uploader>

        <asset-editor
            v-if="showAssetEditor"
            :id="editedAssetId"
            :read-only="!canEdit"
            @closed="closeAssetEditor"
            @saved="assetSaved"
        />

        <create-folder
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
import CreateFolder from './CreateFolder.vue';
import Grid from './Grid.vue';
import Table from './Table.vue';
import HasPagination from '../../data-list/HasPagination';
import HasPreferences from '../../data-list/HasPreferences';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';
import HasActions from '../../data-list/HasActions';
import { keyBy, sortBy } from 'lodash-es';

export default {
    mixins: [HasActions, HasPagination, HasPreferences],

    components: {
        AssetThumbnail,
        AssetEditor,
        Breadcrumbs,
        Uploader,
        Uploads,
        CreateFolder,
        Grid,
        Table,
    },

    props: {
        // The container to display, determined by a parent component.
        // Either the ID, or the whole container object.
        initialContainer: {},
        selectedPath: String, // The path to display, determined by a parent component.
        restrictFolderNavigation: Boolean, // Whether to restrict to a single folder and prevent navigation.
        selectedAssets: Array,
        maxFiles: Number,
        queryScopes: Array,
        initialEditingAssetId: String,
        autoselectUploads: Boolean,
        autofocusSearch: Boolean,
        allowSelectingExistingUpload: Boolean,
    },

    data() {
        return {
            columns: [
                { label: __('File'), field: 'basename', visible: true, sortable: true },
                { label: __('Size'), field: 'size', value: 'size_formatted', visible: true, sortable: true },
                {
                    label: __('Last Modified'),
                    field: 'last_modified',
                    value: 'last_modified_relative',
                    visible: true,
                    sortable: true,
                },
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
            lastItemClicked: null,
            actionOpened: null,
        };
    },

    computed: {
        selectedContainer() {
            return typeof this.initialContainer === 'object' ? this.initialContainer.id : this.initialContainer;
        },

        actionContext() {
            return { container: this.selectedContainer };
        },

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        canEdit() {
            return this.can('edit ' + this.container.id + ' assets') || this.can('configure asset containers');
        },

        canUpload() {
            return (
                this.folder &&
                this.container.allow_uploads &&
                (this.can('upload ' + this.container.id + ' assets') || this.can('configure asset containers'))
            );
        },

        canCreateFolders() {
            return (
                this.folder &&
                this.container.create_folders &&
                !this.restrictFolderNavigation &&
                (this.can('upload ' + this.container.id + ' assets') || this.can('configure asset containers'))
            );
        },

        parameters() {
            return {
                page: this.page,
                perPage: this.perPage,
                sort: this.sortColumn,
                order: this.sortDirection,
                search: this.searchQuery,
                queryScopes: this.queryScopes,
            };
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
            return this.assets.length === 0 && this.folders.length === 0 && (!this.folder || !this.folder.parent_path);
        },

        editedAssetBasename() {
            let asset = this.assets.find((asset) => asset.id == this.editedAssetId);

            return asset ? asset.basename : null;
        },

        modeClass() {
            return 'mode-' + this.mode;
        },
    },

    mounted() {
        this.loadContainers();
    },

    created() {
        this.$events.$on('editor-action-started', this.actionStarted);
        this.$events.$on('editor-action-completed', this.actionCompleted);
    },

    unmounted() {
        this.$events.$off('editor-action-started', this.actionStarted);
        this.$events.$off('editor-action-completed', this.actionCompleted);
    },

    watch: {
        initialContainer() {
            this.container = this.initialContainer;
        },

        container(container) {
            this.initializing = true;
            this.preferencesPrefix = `assets.${container.id}`;
            this.mode = this.getPreference('mode') || 'table';
            this.setInitialPerPage();
            this.loadAssets();
        },

        path() {
            this.loadAssets();
        },

        selectedPath(selectedPath) {
            // The selected path might change from outside due to a popstate navigation
            if (!selectedPath.endsWith('/edit')) {
                this.path = selectedPath;
            }
        },

        parameters(after, before) {
            if (this.initializing || JSON.stringify(before) === JSON.stringify(after)) return;
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
                ? [this.path, this.editedAssetBasename].filter((value) => value != '/').join('/') + '/edit'
                : this.path;

            this.$emit('navigated', this.container, path);
        },

        searchQuery() {
            this.page = 1;
        },
    },

    methods: {
        afterActionSuccessfullyCompleted() {
            this.loadAssets();
        },

        loadContainers() {
            this.$axios.get(cp_url('asset-containers')).then((response) => {
                this.containers = keyBy(response.data, 'id');
                this.container = this.containers[this.selectedContainer];
            });
        },

        loadAssets() {
            this.loading = true;

            const url = this.searchQuery
                ? cp_url(
                      `assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ''}`,
                  ).replace(/\/$/, '')
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`).replace(/\/$/, '');

            this.$axios
                .get(url, { params: this.parameters })
                .then((response) => {
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
                })
                .catch((e) => {
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

        existingUploadSelected(upload) {
            const path = `${this.folder.path}/${upload.basename}`.replace(/^\/+/, '');
            const id = `${this.container.id}::${path}`;

            this.selectedAssets.push(id);
            this.$emit('selections-updated', this.selectedAssets);
        },

        openFileBrowser() {
            this.$refs.uploader.browse();
        },

        folderCreated(folder) {
            this.folders.push(folder);
            this.folders = sortBy(this.folders, 'title');
            this.creatingFolder = false;
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
            this.$refs.browser.focus();

            if (this.maxFiles === 1) {
                this.selectedAssets = [id];
            } else if (i != -1) {
                this.selectedAssets.splice(i, 1);
            } else if (!this.reachedSelectionLimit) {
                if ($event.shiftKey && this.lastItemClicked !== null) {
                    this.selectRange(Math.min(this.lastItemClicked, index), Math.max(this.lastItemClicked, index));
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
            for (var i = from; i <= to; i++) {
                let asset = this.assets[i].id;
                if (!this.selectedAssets.includes(asset) && !this.reachedSelectionLimit) {
                    this.selectedAssets.push(asset);
                }
                this.$emit('selections-updated', this.selectedAssets);
            }
        },

        shiftDown() {
            this.shifting = true;
        },

        clearShift() {
            this.shifting = false;
        },
    },
};
</script>
