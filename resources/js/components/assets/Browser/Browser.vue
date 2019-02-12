<template>

    <div>
        <uploader
            ref="uploader"
            :container="container.id"
            :path="path"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="relative">
                <div class="drag-notification" v-show="dragging">
                    <i class="icon icon-download" />
                    Drop to upload.
                </div>

                <div class="publish-tabs tabs rounded-none rounded-t -mx-1px" v-if="!restrictNavigation && Object.keys(containers).length > 1">
                    <a v-for="item in containers" :key="item.id"
                        v-text="item.title"
                        :class="{ active: item.id === container.id }"
                        @click="selectContainer(item.id)"
                    />
                </div>

                <div v-if="initializing || loadingContainers" class="asset-browser-loading loading">
                    <loading-graphic />
                </div>

                <data-list
                    v-if="!loadingContainers && !initializing"
                    :rows="assets"
                    :columns="columns"
                    :visible-columns="visibleColumns"
                    :search-query="searchQuery"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    @selections-updated="(ids) => $emit('selections-updated', ids)"
                >
                    <div slot-scope="{ filteredRows: rows }">
                        <div class="card p-0">

                            <div class="data-list-header">
                                <data-list-toggle-all ref="toggleAll" />
                                <data-list-search v-model="searchQuery" />

                                <button
                                    class="btn btn-icon-only antialiased ml-2 dropdown-toggle relative"
                                    @click="openFileBrowser"
                                >
                                    <svg-icon name="filter" class="h-4 w-4 mr-1 text-current"></svg-icon>
                                    <span>{{ __('Upload') }}</span>
                                </button>
                            </div>

                            <data-list-bulk-actions
                                v-if="hasActions"
                                :url="actionUrl"
                                :actions="actions"
                                @started="actionStarted"
                                @completed="actionCompleted"
                            />

                            <uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                class="-mt-px"
                            />

                            <data-list-table :loading="loadingAssets" :rows="rows" :allow-bulk-actions="true">

                                <template slot="tbody-start">
                                    <tr v-if="folder.parent_path && !restrictNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.parent_path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-6 h-6 mr-1 inline-block"></file-icon>
                                                ..
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
                                    </tr>
                                    <tr v-for="folder in folders" :key="folder.path" v-if="!restrictNavigation">
                                        <td />
                                        <td @click="selectFolder(folder.path)">
                                            <a class="flex items-center cursor-pointer">
                                                <file-icon extension="folder" class="w-6 h-6 mr-1 inline-block"></file-icon>
                                                {{ folder.title || folder.path }}
                                            </a>
                                        </td>
                                        <td :colspan="columns.length" />
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

                        </div>

                        <data-list-pagination
                            class="mt-3"
                            :resource-meta="meta"
                            @page-selected="page = $event"
                        />

                        <div v-if="assets.length === 0" class="border-t p-2 pl-4 text-sm text-grey-light">
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
            @deleted="assetDeleted">
        </asset-editor>

    </div>

</template>

<script>
import axios from 'axios';
import AssetThumbnail from './Thumbnail.vue';
import AssetEditor from '../Editor/Editor.vue';
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
    },

    props: {
        // The container to display, determined by a parent component.
        // Either the ID, or the whole container object.
        initialContainer: {},
        selectedPath: String,        // The path to display, determined by a parent component.
        restrictNavigation: Boolean,  // Whether to restrict to a single folder and prevent navigation.
        selectedAssets: Array,
        maxFiles: Number,
    },

    data() {
        return {
            columns: ['basename', 'size_b', 'last_modified_timestamp'],
            visibleColumns: ['basename', 'size_b', 'last_modified_timestamp'],
            loadingContainers: true,
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
            uploads: [],
            page: 1,
            perPage: 25, // TODO: Should come from the controller, or a config.
            meta: {},
        }
    },

    computed: {

        selectedContainer() {
            return (typeof this.initialContainer === 'object')
                ? this.initialContainer.id
                : this.initialContainer;
        },

        loading() {
            return this.loadingAssets || this.loadingContainers;
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
                perPage: this.perPage
            }
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

    },

    methods: {

        actionStarted() {
            this.loadingAssets = true;
        },

        actionCompleted() {
            this.loadAssets();
        },

        loadContainers() {
            this.loadingContainers = true;

            axios.get(cp_url('asset-containers')).then(response => {
                this.containers = _.chain(response.data).indexBy('id').value();
                this.container = this.containers[this.selectedContainer];
                this.loadingContainers = false;
            });
        },

        loadAssets() {
            this.loadingAssets = true;
            const url = cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`.trim('/'));

            axios.get(url, { params: this.parameters }).then(response => {
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
    }

}
</script>
