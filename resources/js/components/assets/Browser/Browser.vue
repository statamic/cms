<template>

    <div class="asset-browser card"
         @dragover="dragOver"
         @dragleave="dragStop"
         @drop="dragStop">

        <div v-if="! initialized" class="asset-browser-loading loading">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <div class="drag-notification" v-show="draggingFile">
            <i class="icon icon-download"></i>
            <h3>{{ translate('cp.drop_to_upload') }}</h3>
        </div>

        <div v-if="showSidebar" class="asset-browser-sidebar">
            <h4>Containers</h4>
            <div v-for="c in containers" class="sidebar-item" :class="{ 'active': container.id == c.id }">
                <a @click.prevent="selectContainer(c.id)">
                    {{ c.title }}
                </a>
            </div>
        </div>

        <div class="asset-browser-main" v-if="initialized">

            <div class="asset-browser-header">
                <h1 class="mb-24">
                    <template v-if="isSearching">
                        {{ translate('cp.search_results') }}
                    </template>
                    <template v-else>
                        <template v-if="restrictNavigation">
                            {{ folder.title || folder.path }}
                        </template>
                        <template v-else>
                            {{ container.title }}
                        </template>
                    </template>

                    <div class="loading-indicator" v-show="loadingAssets">
                        <span class="icon icon-circular-graph animation-spin"></span>
                    </div>
                </h1>

                <input type="text"
                    class="search filter-control mb-24"
                    placeholder="{{ translate('cp.search') }}..."
                    v-model="searchTerm"
                    debounce="500" />

                <div class="asset-browser-actions flexy wrap">

                    <slot name="contextual-actions" v-if="selectedAssets.length"></slot>

                    <div class="btn-group action mb-24">
                        <button type="button"
                                class="btn btn-icon"
                                :class="{'depressed': displayMode == 'grid'}"
                                @click="setDisplayMode('grid')">
                            <span class="icon icon-grid"></span>
                        </button>
                        <button type="button"
                                class="btn btn-icon"
                                :class="{'depressed': displayMode == 'table'}"
                                @click="setDisplayMode('table')">
                            <span class="icon icon-list"></span>
                        </button>
                    </div>

                    <div class="btn-group action mb-24">
                        <button type="button"
                                class="btn"
                                v-if="!restrictNavigation && !isSearching"
                                @click.prevent="createFolder">
                            {{ translate('cp.new_folder') }}
                        </button>
                        <button type="button" class="btn" @click.prevent="uploadFile" v-if="!isSearching">
                            {{ translate('cp.upload') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="asset-browser-content">

                <uploader
                    v-ref:uploader
                    :dom-element="uploadElement"
                    :container="container.id"
                    :path="path"
                    @updated="uploadsUpdated"
                    @upload-complete="loadAssets">
                </uploader>

                <uploads
                    v-if="uploads.length"
                    :uploads="uploads">
                </uploads>

                <component
                    :is="listingComponent"
                    :container="container.id"
                    :assets="assets"
                    :folder="folder"
                    :subfolders="subfolders"
                    :loading="loading"
                    :selected-assets="selectedAssets"
                    :restrict-navigation="restrictNavigation"
                    @folder-selected="folderSelected"
                    @folder-editing="editFolder"
                    @folder-deleted="folderDeleted"
                    @asset-selected="assetSelected"
                    @asset-deselected="assetDeselected"
                    @asset-editing="editAsset"
                    @asset-deleting="deleteAsset"
                    @assets-dragged-to-folder="assetsDraggedToFolder"
                    @asset-doubleclicked="assetDoubleclicked">
                </component>

                <div class="no-results" v-if="isEmpty">
                    <template v-if="isSearching">
                        <span class="icon icon-magnifying-glass"></span>
                        <h2>{{ translate('cp.no_search_results') }}</h2>
                    </template>
                    <template v-else>
                        <span class="icon icon-folder"></span>
                        <h2>{{ translate('cp.asset_folder_empty_heading') }}</h2>
                        <h3>{{ translate('cp.asset_folder_empty') }}</h3>
                    </template>
                </div>

                <pagination
                    v-if="pagination.totalPages > 1"
                    :total="pagination.totalPages"
                    :current="pagination.currentPage"
                    :segments="pagination.segments"
                    @selected="paginationPageSelected">
                </pagination>

                <breadcrumbs
                    v-if="!restrictNavigation && !isSearching"
                    :path="path"
                    @navigated="folderSelected">
                </breadcrumbs>

            </div>

            <asset-editor
                v-if="showAssetEditor"
                :id="editedAssetId"
                :has-child.sync="editorHasChild"
                @closed="closeAssetEditor"
                @saved="assetSaved"
                @deleted="assetDeleted"
                @moved="assetMoved">
            </asset-editor>

            <folder-editor
                v-if="showFolderCreator"
                :create="true"
                :container="container.id"
                :path="path"
                @closed="folderCreatorClosed"
                @created="folderCreated">
            </folder-editor>

            <folder-editor
                v-if="showFolderEditor"
                :create="false"
                :container="container.id"
                :path="editedFolderPath"
                @closed="folderEditorClosed"
                @updated="loadAssets">
            </folder-editor>

        </div>


    </div>

</template>

<script>
import DetectsFileDragging from '../../DetectsFileDragging';

module.exports = {

    components: {
        GridListing: require('./Listing/GridListing.vue'),
        TableListing: require('./Listing/TableListing.vue'),
        Uploader: require('../Uploader.vue'),
        Uploads: require('../Uploads.vue'),
        AssetEditor: require('../Editor/Editor.vue'),
        FolderEditor: require('./FolderEditor.vue'),
        Breadcrumbs: require('./Breadcrumbs.vue')
    },


    mixins: [DetectsFileDragging],


    props: [
        'selectedContainer',   // The ID of the container to display, determined by a parent component.
        'selectedPath',        // The path to display, determined by a parent component.
        'restrictNavigation',  // Whether to restrict to a single folder and prevent navigation.
        'selectedAssets',
        'maxFiles'
    ],


    data() {
        return {
            loadingAssets: true,
            initializedAssets: false,
            loadingContainers: true,
            containers: null,
            container: null,
            path: null,
            assets: [],
            folders: [],
            folder: {},
            displayMode: 'table',
            uploads: [],
            draggingFile: false,
            pagination: {},
            selectedPage: 1,
            editedAssetId: null,
            showFolderCreator: false,
            editedFolderPath: null,
            editorHasChild: false,
            isSearching: false
        }
    },


    computed: {

        initialized() {
            return this.initializedAssets && !this.loadingContainers;
        },

        loading() {
            return this.loadingAssets || this.loadingContainers;
        },

        /**
         * Whether the current folder has assets.
         */
        hasAssets() {
            return this.assets.length > 0;
        },

        hasSubfolders() {
            return this.subfolders.length > 0;
        },

        isEmpty() {
            return !this.hasAssets && !this.hasSubfolders;
        },

        showSidebar() {
            if (! this.initialized) return false;

            if (this.isSearching) return false;

            if (this.restrictNavigation) return false;

            return Object.keys(this.containers).length > 1;
        },

        listingComponent() {
            return (this.displayMode === 'grid') ? 'GridListing' : 'TableListing';
        },

        fullPath() {
            if (! this.container) return;

            let fullPath = this.container.id;

            if (this.path !== '/') {
                fullPath += '/' + this.path;
            }

            return fullPath;
        },

        subfolders() {
            if (this.restrictNavigation) return [];

            return this.folders;
        },

        uploadElement() {
            return this.$el;
        },

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        showFolderEditor() {
            return this.editedFolderPath !== null;
        },

        maxFilesReached() {
            return this.maxFiles
                && this.selectedAssets.length >= this.maxFiles
        }

    },


    ready() {
        this.path = this.selectedPath;

        // We need all the containers since they'll be displayed in the sidebar. This will also load
        // up the current container object using the initial container id. Setting the container
        // property will trigger loading of assets since there's a watcher reacting to it.
        this.loadContainers();

        this.displayMode = Cookies.get('statamic.assets.listing_view_mode') || 'table';
    },


    events: {

        'close-editor': function() {
            if (this.editorHasChild) {
                return this.$broadcast('close-child-editor');
            }

            this.showFolderCreator = false;
            this.editedAssetId = null;
            this.editedFolderPath = null;
        },

        'refresh-assets': function() {
            this.loadAssets();
        },

        'delete-assets': function(ids) {
            this.deleteAsset(ids);
        },

    },


    watch: {

        /**
         * Whenever the fullPath computed property is changed, it means
         * that either the path or the container has been modified,
         * so then a new set of assets should be displayed.
         */
        fullPath() {
            this.loadAssets();
        },

        /**
         * When the selected container prop has changed, the parent component
         * has indicated that a different set of assets should be shown.
         */
        selectedContainer(container) {
            this.container = this.containers[container];
        },

        /**
         * When the selected path prop has changed, the parent component
         * has indicated that a different set of assets should be shown.
         */
        selectedPath(path) {
            this.path = path;
        },

        /**
         * When selected assets are updated/modified, the parent component should be notified.
         */
        selectedAssets(selections) {
            this.$emit('selections-updated', selections);
        },

        searchTerm(term) {
            if (term) {
                this.search();
            } else {
                this.loadAssets();
            }
        }

    },


    methods: {

        /**
         * Load asset container data
         */
        loadContainers() {
            this.$http.get(cp_url('assets/containers/get')).success((response) => {
                // Set the containers property to a collection of the items in the response.
                // We are only interested in certain keys, and we want them indexed by
                // ID to make retrieving container values simpler down the road.
                this.containers = _.chain(response.items).map((container) => {
                    return _.pick(container, 'id', 'title');
                }).indexBy('id').value();

                // We need the container property to be the retrieved data object.
                this.container = this.containers[this.selectedContainer];

                this.loadingContainers = false;
            });
        },

        /**
         * Load assets from the container and folder specified
         */
        loadAssets(page) {
            this.loadingAssets = true;

            this.$http.post(cp_url('assets/browse'), {
                container: this.container.id,
                path: this.path,
                page: this.selectedPage
            }).success((response) => {
                this.assets = response.assets;
                this.folders = response.folders;
                this.folder = response.folder;
                this.pagination = response.pagination;
                this.selectedPage = response.pagination.currentPage;
                this.loadingAssets = false;
                this.initializedAssets = true;
                this.isSearching = false;
            });
        },

        search() {
            this.loadingAssets = true;

            this.$http.post(cp_url('assets/search'), {
                term: this.searchTerm,
                container: this.container.id,
                folder: this.folder.path,
                restrictNavigation: this.restrictNavigation
            }).success((response) => {
                this.isSearching = true;
                this.assets = response.assets;
                this.folders = [];
                this.loadingAssets = false;
                this.initializedAssets = true;
            });
        },

        /**
         * When a folder was selected from within listing component.
         */
        folderSelected(path) {
            // Trigger re-loading of assets in the selected folder.
            this.path = path;

            // Trigger an event so the parent can do something.
            // eg. The asset manager would want to change the browser URL.
            this.$emit('navigated', this.container.id, this.path);
        },

        /**
         * When a container is selected/clicked in the sidebar
         */
        selectContainer(container) {
            // Trigger re-loading of assets in the selected container.
            this.container = this.containers[container];
            this.path = '/';

            // Trigger an event so the parent can do something.
            // eg. The asset manager would want to change the browser URL.
            this.$emit('navigated', this.container.id, this.path);
        },

        /**
         * When an asset has been selected.
         */
        assetSelected(id) {
            // For single asset selections, clicking a different asset will replace the selection.
            if (this.maxFiles === 1 && this.maxFilesReached) {
                this.selectedAssets = [id];
            }

            // Completely prevent additional selections when the limit has been hit.
            if (this.maxFilesReached) {
                return;
            }

            // Don't add the same asset twice.
            if (_(this.selectedAssets).contains(id)) {
                return;
            }

            this.selectedAssets.push(id);

            // For some reason, Vue wasn't reacting to new item.
            // It would show up in the data, but wouldn't adjust the view.
            // Mapping over itself fixes this. ¯\_(ツ)_/¯
            this.selectedAssets = _(this.selectedAssets).map(val => val);
        },

        /**
         * When an asset has been deselected.
         */
        assetDeselected(id) {
            this.selectedAssets = _(this.selectedAssets).without(id);
        },

        /**
         * When an asset has been chosen for editing.
         */
        editAsset(id) {
            this.editedAssetId = id;
        },

        /**
         * Delete the given asset and refresh the browser.
         */
        deleteAsset(ids) {
            ids = Array.isArray(ids) ? ids : [ids];

            swal({
                type: 'warning',
                title: translate('cp.are_you_sure'),
                text: translate_choice('cp.confirm_delete_items', ids),
                confirmButtonText: translate('cp.yes_im_sure'),
                cancelButtonText: translate('cp.cancel'),
                showCancelButton: true
            }, () => {
                const url = cp_url('assets/delete');

                this.$http.delete(url, { ids: ids }).success((response) => {
                    this.loadAssets();
                    this.selectedAssets = _(this.selectedAssets).difference(ids);
                });
            });
        },

        /**
         * Close the asset editor.
         */
        closeAssetEditor() {
            this.editedAssetId = null;
        },

        /**
         * When an asset has been saved from the editor.
         */
        assetSaved() {
            this.closeAssetEditor();
            this.loadAssets();
        },

        /**
         * When an asset was deleted from the editor.
         */
        assetDeleted() {
            this.closeAssetEditor();
            this.loadAssets();
        },

        /**
         * When an asset was moved to another folder from the editor.
         */
        assetMoved(folder) {
            this.closeAssetEditor();
            this.folderSelected(folder);
        },

        /**
         * When an asset was double clicked.
         *
         * This event would only ever be called when the browser is used in the context of a
         * fieldtype. When used in the "Assets" section, the double click would be handled
         * from within the asset component and caused the edit dialog to be opened.
         */
        assetDoubleclicked(id) {
            this.assetSelected(id);
            this.$emit('asset-doubleclicked');
        },

        /**
         * Show the file upload finder window.
         */
        uploadFile() {
            this.$refs.uploader.browse();
        },

        /**
         * When a page was selected in the pagination.
         */
        paginationPageSelected(page) {
            this.selectedPage = page;
            this.loadAssets();
        },

        createFolder() {
            this.showFolderCreator = true;
        },

        folderCreatorClosed() {
            this.showFolderCreator = false;
        },

        folderCreated(path) {
            this.folderSelected(path)
        },

        editFolder(folder) {
            this.editedFolderPath = folder;
        },

        folderEditorClosed() {
            this.editedFolderPath = null;
        },

        folderDeleted(folder) {
            this.loadAssets();
        },

        uploadsUpdated(uploads) {
            this.$set('uploads', uploads);
        },

        /**
         * Set the display mode and remember it in a cookie
         */
        setDisplayMode(mode) {
            this.displayMode = mode;
            Cookies.set('statamic.assets.listing_view_mode', mode);
        },

        assetsDraggedToFolder(folder) {
            const url = cp_url('/assets/move');

            const payload = {
                assets: this.selectedAssets,
                folder: folder,
                container: this.container.id
             };

            this.$http.post(url, payload).success((response) => {
                this.loadAssets();
                this.selectedAssets = [];
            });
        }

    }

};
</script>
