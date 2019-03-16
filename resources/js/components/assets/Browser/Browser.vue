<template>

    <div>
        <div v-if="initializing" class="loading">
            <loading-graphic  />
        </div>

        <uploader
            v-if="!initializing"
            ref="uploader"
            :container="container.id"
            :path="path"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="relative" :class="{ 'shadow': showContainerTabs }">
                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="h-12 w-12 mb-2" />
                    {{ __('Drop File to Upload') }}
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
                    :search-query="searchQuery"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    :sort="false"
                    :sort-column="sortColumn"
                    :sort-direction="sortDirection"
                    @selections-updated="(ids) => $emit('selections-updated', ids)"
                >
                    <div slot-scope="{ filteredRows: rows }">
                        <div class="card p-0" :class="{ 'rounded-t-none shadow-none': showContainerTabs }">

                            <div class="data-list-header">
                                <data-list-toggle-all ref="toggleAll" v-if="!hasMaxFiles" />
                                <data-list-search v-model="searchQuery" />

                                <button
                                    class="btn btn-flat btn-icon-only ml-2 dropdown-toggle relative"
                                    @click="creatingFolder = true"
                                >
                                    <svg-icon name="folder-add" class="h-4 w-4 mr-1" />
                                    <span>{{ __('Create Folder') }}</span>
                                </button>

                                <button
                                    class="btn btn-flat btn-icon-only ml-2 dropdown-toggle relative"
                                    @click="openFileBrowser"
                                >
                                    <svg-icon name="upload" class="h-4 w-4 mr-1 text-current" />
                                    <span>{{ __('Upload') }}</span>
                                </button>
                            </div>

                            <uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                class="-mt-px"
                            />

                            <data-list-table :loading="loadingAssets" :rows="rows" :allow-bulk-actions="true" @sorted="sorted">

                                <template slot="tbody-start">
                                    <tr v-if="folder.parent_path && !restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.parent_path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-6 h-6 mr-1 inline-block"></file-icon>
                                                ..
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
                                    </tr>
                                    <tr v-for="(folder, i) in folders" :key="folder.path" v-if="!restrictFolderNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-6 h-6 mr-1 inline-block"></file-icon>
                                                {{ folder.path }}
                                            </a>
                                        </td>
                                        <td class="text-right" :colspan="columns.length">
                                            <dropdown-list>
                                                <ul class="dropdown-menu">
                                                    <li><a @click="editedFolderPath = folder.path" v-text="__('Edit')"></a></li>
                                                </ul>
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
                                    <div class="flex items-center" @dblclick="$emit('asset-doubleclicked', asset)">
                                        <asset-thumbnail :asset="asset" class="w-6 h-6 mr-1" />
                                        <label :for="checkboxId" class="cursor-pointer select-none">{{ asset.title || asset.basename }}</label>
                                    </div>
                                </template>

                                <template slot="actions" slot-scope="{ row: asset }">
                                    <dropdown-list>
                                        <div class="dropdown-menu">
                                            <li><a @click="edit(asset.id)">Edit</a></li>
                                            <div class="li divider" />
                                            <data-list-inline-actions
                                                :item="asset.id"
                                                :url="actionUrl"
                                                :actions="actions"
                                                @started="actionStarted"
                                                @completed="actionCompleted"
                                            />
                                        </div>
                                    </dropdown-list>
                                </template>

                            </data-list-table>

                            <data-list-bulk-actions
                                class="rounded-b"
                                v-if="hasActions"
                                :url="actionUrl"
                                :actions="actions"
                                @started="actionStarted"
                                @completed="bulkActionsCompleted"
                            />

                        </div>

                        <data-list-pagination
                            class="mt-3"
                            :resource-meta="meta"
                            @page-selected="page = $event"
                        />

                        <div v-if="assets.length === 0" class="border-t p-2 pl-4 text-sm text-grey-70">
                            There are no assets.
                        </div>

                    </div>
                </data-list>
            </div>
        </uploader>

        <asset-editor
            v-if="showAssetEditor"
            :id="editedAssetId"
            @closed="closeAssetEditor"
            @saved="assetSaved"
            @deleted="assetDeleted"
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
import HasActions from '../../data-list/HasActions';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';

export default {

    mixins: [
        HasActions,
    ],

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
            assets: [],
            path: this.selectedPath,
            folders: [],
            folder: {},
            searchQuery: '',
            editedAssetId: null,
            editedFolderPath: null,
            creatingFolder: false,
            uploads: [],
            page: 1,
            perPage: 25, // TODO: Should come from the controller, or a config.
            meta: {},
            sortColumn: 'basename',
            sortDirection: 'asc',
        }
    },

    computed: {

        selectedContainer() {
            return (typeof this.initialContainer === 'object')
                ? this.initialContainer.id
                : this.initialContainer;
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

        parameters() {
            return {
                page: this.page,
                perPage: this.perPage,
                sort: this.sortColumn,
                order: this.sortDirection,
            }
        },

        hasMaxFiles() {
            return this.maxFiles !== undefined && this.maxFiles !== Infinity;
        }

    },

    mounted() {
        this.loadContainers();
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
        }

    },

    methods: {

        actionStarted() {
            this.loadingAssets = true;
        },

        actionCompleted() {
            this.loadAssets();
        },

        bulkActionsCompleted() {
            this.$refs.toggleAll.uncheckAllItems();
            this.actionCompleted();
        },

        loadContainers() {
            this.$axios.get(cp_url('asset-containers')).then(response => {
                this.containers = _.chain(response.data).indexBy('id').value();
                this.container = this.containers[this.selectedContainer];
            });
        },

        loadAssets() {
            this.loadingAssets = true;
            const url = cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`.trim('/'));

            this.$axios.get(url, { params: this.parameters }).then(response => {
                this.assets = response.data.data;
                this.folders = response.data.meta.folders;
                this.folder = response.data.meta.folder;
                this.meta = response.data.meta;
                this.loadingAssets = false;
                this.initializing = false;
            }).catch(e => {
                this.$notify.error(e.response.data.message, { dismissible: false });
                this.assets = [];
                this.folders = [];
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

        destroy(id) {
            // TODO
            console.log('deleting asset');
        },

        destroyMultiple(ids) {
            // TODO
            console.log('deleting multiple assets', ids);
        },

        uploadsUpdated(uploads) {
            this.uploads = uploads;
        },

        uploadCompleted(asset) {
            this.loadAssets();
            this.$notify.success(__(':file uploaded', { file: asset.basename }), { timeout: 3000 });
        },

        uploadError(upload, uploads) {
            this.uploads = uploads;
            this.$notify.error(upload.errorMessage);
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
    }

}
</script>
