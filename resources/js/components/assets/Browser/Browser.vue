<template>

    <div>

        <div class="publish-tabs tabs rounded-none rounded-t -mx-1px" v-if="!restrictNavigation && Object.keys(containers).length > 1">
            <a v-for="item in containers" :key="item.id"
                v-text="item.title"
                :class="{ active: item.id === container.id }"
                @click="selectContainer(item.id)"
            />
        </div>

        <div v-if="loading" class="asset-browser-loading loading">
            <loading-graphic />
        </div>

        <data-list
            v-if="!loading"
            :rows="assets"
            :columns="columns"
            :visible-columns="visibleColumns"
            :search-query="searchQuery"
            :selections="selectedAssets"
            :max-selections="maxFiles"
            @selections-updated="(ids) => $emit('selections-updated', ids)"
        >
            <div slot-scope="{ filteredRows: rows }">

                <div class="data-list-header">
                    <data-list-toggle-all />
                    <data-list-search v-model="searchQuery" />
                    <data-list-bulk-actions>
                        <div slot-scope="{ selections, hasSelections }">
                            <div class="flex items-center" v-if="hasSelections">
                                <div class="text-xs text-grey-light mr-2">{{ selections.length }} selected</div>
                                <slot name="actions" :selections="selections" />
                            </div>
                            <div v-show="!hasSelections">
                                <button class="btn">New Folder</button>
                                <button class="btn ml-1">Upload</button>
                            </div>
                        </div>
                    </data-list-bulk-actions>
                </div>

                <data-table :rows="rows" :allow-bulk-actions="true">

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
                                    {{ folder.path }}
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
                            <li><a @click="edit(asset.id)">Edit</a></li>
                            <li class="warning"><a @click="destroy(asset.id)">Delete</a></li>
                        </dropdown-list>
                    </template>

                </data-table>

                <div v-if="assets.length === 0" class="border-t p-2 pl-4 text-sm text-grey-light">
                    There are no assets.
                </div>

            </div>
        </data-list>

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

export default {

    components: {
        AssetThumbnail,
        AssetEditor,
    },

    props: [
        // The container to display, determined by a parent component.
        // Either the ID, or the whole container object.
        'initialContainer',

        'selectedPath',        // The path to display, determined by a parent component.

        'restrictNavigation',  // Whether to restrict to a single folder and prevent navigation.
        'selectedAssets',
        'maxFiles'
    ],

    data() {
        return {
            columns: ['basename', 'size_b', 'last_modified_timestamp'],
            visibleColumns: ['basename', 'size_b', 'last_modified_timestamp'],
            loadingContainers: true,
            containers: [],
            container: {},
            loadingAssets: true,
            assets: [],
            path: this.selectedPath,
            folders: [],
            folder: {},
            searchQuery: '',
            editedAssetId: null,
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
        }

    },

    methods: {

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

            axios.get(url).then(response => {
                const { assets, folders, folder } = response.data;
                this.assets = assets;
                this.folders = folders;
                this.folder = folder;
                this.loadingAssets = false;
            }).catch(e => {
                this.$notify.error(e.response.data.message, { dismissible: false });
                this.loadingAssets = false;
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
        }
    }

}
</script>
