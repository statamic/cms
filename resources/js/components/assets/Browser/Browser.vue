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
                    <svg-icon name="upload" class="h-12 w-12 m-4" />
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
                    @visible-columns-updated="visibleColumns = $event"
                >
                    <div slot-scope="{ filteredRows: rows }" :class="modeClass">
                        <div class="card overflow-hidden p-0" :class="{ 'select-none' : shifting }">
                            <div class="relative w-full">

                                <div class="flex items-center justify-between p-2 text-sm">
                                    <data-list-search class="h-8" ref="search" v-model="searchQuery" />

                                    <button v-if="canCreateFolders" class="btn btn-sm rtl:mr-3 ltr:ml-3" @click="creatingFolder = true">
                                        <svg-icon name="folder-add" class="h-4 w-4 rtl:ml-2 ltr:mr-2" />
                                        <span>{{ __('Create Folder') }}</span>
                                    </button>

                                    <button v-if="canUpload" class="btn btn-sm rtl:mr-3 ltr:ml-3" @click="openFileBrowser">
                                        <svg-icon name="upload" class="h-4 w-4 rtl:ml-2 ltr:mr-2 text-current" />
                                        <span>{{ __('Upload') }}</span>
                                    </button>

                                    <data-list-column-picker v-if="mode === 'table'" class="rtl:mr-3 ltr:ml-3" :preferences-key="preferencesKey('columns')" />

                                    <div class="btn-group rtl:mr-3 ltr:ml-3">
                                        <button class="btn btn-sm" @click="setMode('grid')" :class="{'active': mode === 'grid'}">
                                            <svg-icon name="assets-mode-grid" class="h-4 w-4"/>
                                        </button>
                                        <button class="btn btn-sm" @click="setMode('table')" :class="{'active': mode === 'table'}">
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
                                :allow-selecting-existing="allowSelectingExistingUpload"
                                :class="{ '-mt-px': !hasSelections, 'mt-10': hasSelections }"
                                @existing-selected="existingUploadSelected"
                            />

                            <div class="overflow-x-auto overflow-y-hidden">
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
                                                <file-icon extension="folder" class="w-8 h-8 rtl:ml-2 ltr:mr-2 inline-block text-blue-400 group-hover:text-blue" />
                                                ..
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
                                    </tr>
                                    <tr v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <td />

                                        <td v-for="column in visibleColumns">
                                            <template v-if="column.field === 'basename'">
                                                <a class="w-full flex items-center cursor-pointer group" @click="selectFolder(folder.path)">
                                                    <file-icon extension="folder" class="w-8 h-8 rtl:ml-2 ltr:mr-2 inline-block text-blue-400 group-hover:text-blue" />
                                                    {{ folder.basename }}
                                                </a>
                                            </template>
                                        </td>

                                        <th class="actions-column" :colspan="columns.length">
                                            <dropdown-list placement="left-start" v-if="folderActions(folder).length">
                                                <data-list-inline-actions
                                                    :item="folder.path"
                                                    :url="folderActionUrl"
                                                    :actions="folderActions(folder)"
                                                    @started="actionStarted"
                                                    @completed="actionCompleted"
                                                />
                                            </dropdown-list>
                                        </th>
                                    </tr>
                                </template>

                                <template slot="cell-basename" slot-scope="{ row: asset, checkboxId }">
                                    <div class="flex items-center w-fit-content group">
                                        <asset-thumbnail :asset="asset" :square="true" class="w-8 h-8 rtl:ml-2 ltr:mr-2 cursor-pointer" @click.native.stop="$emit('edit-asset', asset)" />
                                        <label :for="checkboxId" class="cursor-pointer select-none group-hover:text-blue normal-nums" @click.stop="$emit('edit-asset', asset)">
                                            {{ asset.basename }}
                                        </label>
                                    </div>
                                </template>

                                <template slot="actions" slot-scope="{ row: asset }">
                                    <dropdown-list placement="left-start">
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
                            </div>

                            <!-- Grid Mode -->
                            <div v-if="mode === 'grid' && ! containerIsEmpty">
                                <div class="asset-grid-listing px-4 pt-2">
                                    <!-- Parent Folder -->
                                    <div class="asset-tile" v-if="(folder && folder.parent_path) && !restrictFolderNavigation">
                                        <div class="asset-thumb-container">
                                            <button @click="selectFolder(folder.parent_path)">
                                                <div class="asset-thumb">
                                                    <file-icon extension="folder" class="w-full h-full text-blue-400 hover:text-blue" />
                                                </div>
                                            </button>
                                        </div>
                                        <div class="asset-meta flex items-center">
                                            <div class="asset-filename text-center w-full px-2 py-1">..</div>
                                        </div>
                                    </div>
                                    <!-- Sub-Folders -->
                                    <div class="asset-tile group relative" v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <div class="asset-thumb-container">
                                            <button @click="selectFolder(folder.path)">
                                                <div class="asset-thumb">
                                                    <file-icon extension="folder" class="w-full h-full text-blue-400 hover:text-blue" />
                                                </div>
                                            </button>
                                        </div>
                                        <div class="asset-meta flex items-center">
                                            <div class="asset-filename text-center w-full px-2 py-1" v-text="folder.basename" :title="folder.basename" />
                                        </div>
                                        <dropdown-list v-if="folderActions(folder).length"
                                            class="absolute top-1 rtl:left-2 ltr:right-2 opacity-0 group-hover:opacity-100"
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
                                    <!-- Assets -->
                                    <button
                                        class="asset-tile outline-none group relative"
                                        v-for="(asset, index) in assets"
                                        :key="asset.id"
                                        :class="{ 'selected': isSelected(asset.id) }"
                                    >
                                        <div
                                            class="w-full"
                                            @click.stop="toggleSelection(asset.id, index, $event)"
                                            @dblclick.stop="$emit('edit-asset', asset)"
                                        >
                                            <div class="asset-thumb-container">
                                                <div class="asset-thumb" :class="{'bg-checkerboard': asset.can_be_transparent}">
                                                    <img v-if="asset.is_image" :src="asset.thumbnail" loading="lazy" :class="{'p-4 h-full w-full': asset.extension === 'svg'}" />
                                                    <file-icon
                                                        v-else
                                                        :extension="asset.extension"
                                                        class="p-4 h-full w-full"
                                                    />
                                                </div>
                                            </div>
                                            <div class="asset-meta">
                                                <div class="asset-filename px-2 py-1 text-center" v-text="asset.basename" :title="asset.basename" />
                                            </div>
                                        </div>
                                        <dropdown-list
                                            class="absolute top-1 rtl:left-2 ltr:right-2 opacity-0 group-hover:opacity-100"
                                            :class="{ 'opacity-100': actionOpened === asset.id }"
                                            @opened="actionOpened = asset.id"
                                            @closed="actionOpened = null"
                                        >
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

                            <div class="p-4 text-gray-700"
                                v-if="containerIsEmpty"
                                v-text="searchQuery ? __('No results') : __('This container is empty')" />

                        </div>

                        <data-list-pagination
                            class="mt-6"
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
        CreateFolder,
    },

    props: {
        // The container to display, determined by a parent component.
        // Either the ID, or the whole container object.
        initialContainer: {},
        selectedPath: String,        // The path to display, determined by a parent component.
        restrictFolderNavigation: Boolean,  // Whether to restrict to a single folder and prevent navigation.
        selectedAssets: Array,
        maxFiles: Number,
        queryScopes: Array,
        initialEditingAssetId: String,
        autoselectUploads: Boolean,
        autofocusSearch: Boolean,
        allowSelectingExistingUpload: Boolean,
        initialColumns: {
            type: Array,
            default: () => [],
        },
    },

    data() {
        return {
            columns: this.initialColumns,
            visibleColumns: this.initialColumns.filter(column => column.visible),
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

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        canEdit() {
            return this.can('edit '+ this.container.id +' assets') || this.can('configure asset containers')
        },

        canUpload() {
            return this.folder && this.container.allow_uploads && (this.can('upload '+ this.container.id +' assets') || this.can('configure asset containers'));
        },

        canCreateFolders() {
            return this.folder && this.container.create_folders && ! this.restrictFolderNavigation && (this.can('upload '+ this.container.id +' assets') || this.can('configure asset containers'));
        },

        parameters() {
            return {
                page: this.page,
                perPage: this.perPage,
                sort: this.sortColumn,
                order: this.sortDirection,
                search: this.searchQuery,
                queryScopes: this.queryScopes,
                columns: this.visibleColumnParameters,
            }
        },

        visibleColumnParameters: {
            get() {
                if (_.isEmpty(this.visibleColumns)) {
                    return null;
                }
                return this.visibleColumns.map(column => column.field).join(',');
            },
            set(value) {
                this.visibleColumns = value.split(',').map(field => this.columns.find(column => column.field === field));
            },
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
        },

        columnShowing(column) {
            return this.visibleColumns.find(c => c.field === column);
        },

    },

    mounted() {
        this.loadContainers();
    },

    created() {
        this.$events.$on('editor-action-started', this.actionStarted);
        this.$events.$on('editor-action-completed', this.actionCompleted);
    },

    destroyed() {
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
                ? [this.path, this.editedAssetBasename].filter(value => value != '/').join('/') + '/edit'
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
                this.columns = data.meta.columns;

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
            this.folders = _.sortBy(this.folders, 'title');
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
            this.$refs.browser.focus()

            if (this.maxFiles === 1) {
                this.selectedAssets = [id];
            } else if (i != -1) {
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
