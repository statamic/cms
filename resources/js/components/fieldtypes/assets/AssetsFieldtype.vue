<template>
    <div
        class="assets-fieldtype"
        :class="{
            'max-files-reached': maxFilesReached,
            'empty': ! assets.length,
            'solo': soloAsset,
        }"
        @dragover="dragOver"
        @dragleave="dragStop"
        @drop="dragStop">

        <div v-if="loading" class="loading loading-basic">
            <loading-graphic :inline="true" />
        </div>

        <div class="drag-notification" v-if="containerSpecified && draggingFile && !showSelector">
            <i class="icon icon-download"></i>
            <h3>{{ __('Drop to Upload') }}</h3>
        </div>

        <template v-if="!loading">

            <div class="manage-assets flex items-center" v-if="!maxFilesReached" :class="{'bard-drag-handle': isInBardField}">

                <div v-if="!containerSpecified">
                    <i class="icon icon-warning"></i>
                    {{ __('cp.no_asset_container_specified') }}
                </div>

                <template v-else>
                    <button
                        type="button"
                        class="btn btn-with-icon mr-1"
                        @click="openSelector"
                        @keyup.space.enter="openSelector"
                        tabindex="0">
                        <span class="icon icon-folder-images"></span>
                        {{ __('Browse Assets') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-with-icon"
                        @click.prevent="uploadFile">
                        <span class="icon icon-upload-to-cloud"></span>
                        {{ __('Upload') }}
                    </button>

                    <p>{{ __('or drag and drop files') }}</p>

                    <button
                        type="button"
                        class="delete-bard-set btn btn-icon float-right"
                        v-if="isInBardField"
                        @click.prevent="$dispatch('asset-field.delete-bard-set')">
                        <span class="icon icon-trash"></span>
                    </button>

                </template>
            </div>

            <!-- <uploader
                v-ref=uploader
                v-if="containerSpecified && !showSelector"
                :dom-element="uploadElement"
                :container="container"
                :path="folder"
                @updated="uploadsUpdated"
                @upload-complete="uploadComplete">
            </uploader>

            <uploads
                v-if="uploads.length"
                :uploads="uploads">
            </uploads> -->

            <template v-if="expanded && ! soloAsset">

                <div class="asset-grid-listing" v-if="displayMode === 'grid'" ref="assets">

                    <asset-tile
                        v-for="asset in assets"
                        :key="asset.id"
                        :asset="asset"
                        @removed="assetRemoved">
                    </asset-tile>

                </div>

                <div class="asset-table-listing" v-if="displayMode === 'list'">

                    <table>
                        <tbody ref="assets">
                            <tr is="assetRow"
                                v-for="asset in assets"
                                :key="asset.id"
                                :asset="asset"
                                @removed="assetRemoved">
                            </tr>
                        </tbody>
                    </table>

                </div>

            </template>

            <div class="asset-solo-container" v-if="expanded && soloAsset" ref="assets">
                <asset-tile
                    v-for="asset in assets"
                    :key="asset.id"
                    :asset="asset"
                    @removed="assetRemoved">
                </asset-tile>
            </div>
        </template>

        <stack
            v-if="showSelector"
            name="asset-selector"
            @closed="closeSelector"
        >
            <selector
                :container="container"
                :folder="folder"
                :restrict-navigation="restrictNavigation"
                :selected="selectedAssets"
                :view-mode="selectorViewMode"
                :max-files="maxFiles"
                @selected="assetsSelected"
                @closed="closeSelector">
            </selector>
        </stack>
    </div>
</template>


<style lang="scss">

    .asset-listing-uploads {
        border: 1px dashed #ccc;
        border-top: 0;
        margin: 0;
        padding: 10px 20px;

        table {
            margin: 0;
        }

        thead {
            display: none;
        }

        tr:first-child {
            border-top: 0;
        }
    }

</style>


<script>
import axios from 'axios';
import DetectsFileDragging from '../../DetectsFileDragging';

export default {

    components: {
        assetTile: require('./AssetTile.vue'),
        assetRow: require('./AssetRow.vue'),
        selector: require('../../assets/Selector.vue'),
        uploader: require('../../assets/Uploader.vue'),
        uploads: require('../../assets/Uploads.vue')
    },


    mixins: [Fieldtype, DetectsFileDragging],


    data() {
        return {
            assets: [],
            loading: true,
            initializing: true,
            showSelector: false,
            selectorViewMode: null,
            draggingFile: false,
            uploads: [],
            innerDragging: false,
            displayMode: 'grid'
        };
    },


    computed: {

        /**
         * Whether any assets have been selected.
         */
        hasAssets() {
            return Boolean(this.assets.length);
        },

        /**
         * The initial container to be displayed in the selector.
         */
        container() {
            return this.config.container;
        },

        /**
         * The initial folder to be displayed in the selector.
         */
        folder() {
            return this.config.folder || '/';
        },

        /**
         * If an asset container has been specified in the config.
         */
        containerSpecified() {
            return this.config.container != null;
        },

        /**
         * Whether assets should be restricted to the specified container
         * and folder. This will prevent navigation to other places.
         */
        restrictNavigation() {
            return this.config.restrict || false;
        },

        /**
         * The maximum number of files allowed.
         */
        maxFiles() {
            if (! this.config.max_files) return Infinity;

            return parseInt(this.config.max_files);
        },

        /**
         * Whether the maximum number of files have been selected.
         */
        maxFilesReached() {
            if (this.maxFiles === 0) return false;

            return this.assets.length >= this.maxFiles;
        },

        /**
         * True if a single asset.
         */
        soloAsset() {
            return this.maxFiles === 1;
        },

        /**
         * The selected assets.
         *
         * The asset browser expects an array of asset IDs to be passed in as a prop.
         */
        selectedAssets() {
            return this.value;
        },

        /**
         * Whether the fieldtype is in the expanded UI state.
         */
        expanded() {
            return this.assets.length > 0;
        },

        /**
         * The DOM element the uploader component will bind to.
         */
        uploadElement() {
            return this.$el;
        },

        isInBardField() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (! parent) return false;

                if (parent.constructor.name === 'BardFieldtype') {
                    return true;
                }

                vm = parent;
            }
        }

    },

    events: {
        'close-selector' () {
            this.closeSelector();
        }
    },

    methods: {

        initializeAssets() {
            if (! this.meta.data) {
                this.loadAssets(this.value);
                this.initializing = false;
                return;
            }

            this.assets = this.meta.data;
            this.$nextTick(() => {
                this.initializing = false;
                this.loading = false;
                this.sortable();
            });
        },

        /**
         * Get asset data from the server
         *
         * Accepts an array of asset URLs and/or IDs.
         */
        loadAssets(assets) {
            this.loading = true;

            if (! assets || ! assets.length) {
                this.loading = false;
                this.assets = [];
                return;
            }

            axios.get(cp_url('assets-fieldtype'), {
                params: { assets }
            }).then(response => {
                this.assets = response.data;
                this.loading = false;
                this.$nextTick(() => this.sortable());
            });
        },

        /**
         * When a user has finished selecting items in the browser.
         *
         * We should update the fieldtype with any selections.
         */
        assetsSelected(selections) {
            this.loadAssets(selections);
        },

        /**
         * Open the asset selector modal
         */
        openSelector() {
            this.showSelector = true;
            this.$root.hideOverflow = true;
        },

        /**
         * Close the asset selector modal
         */
        closeSelector() {
            this.showSelector = false;
            this.$root.hideOverflow = false;
        },

        /**
         * When an asset remove button was clicked.
         */
        assetRemoved(asset) {
            const index = _(this.assets).findIndex({ id: asset.id });
            this.assets.splice(index, 1);
        },

        /**
         * When the uploader component has finished uploading a file.
         */
        uploadComplete(asset) {
            this.assets.push(asset);
        },

        /**
         * When the uploader component has modified the uploads array
         */
        uploadsUpdated(uploads) {
            this.$set('uploads', uploads);
        },

        /**
         * Show the file upload finder window.
         */
        uploadFile() {
            this.$refs.uploader.browse();
        },

        sortable() {
            if (this.maxFiles === 1) return;

            $(this.$refs.assets).sortable({
                items: '> :not(.ghost)',
                start: (e, ui) => {
                    ui.item.data('start', ui.item.index());
                },
                update: (e, ui) => {
                    const start = ui.item.data('start');
                    const end = ui.item.index();

                    this.assets.splice(end, 0, this.assets.splice(start, 1)[0]);
                },
                placeholder: {
                    element(currentItem) {
                        return $("<div class='ui-sortable-placeholder asset-tile'><div class='faux-thumbnail'></div></div>")[0];
                    },
                    update(container, p) {
                        return;
                    }
                }
            });
        },

        getReplicatorPreviewText() {
            return _.map(this.assets, (asset) => {
                return asset.is_image ?
                    `<img src="${asset.thumbnail}" width="20" height="20" title="${asset.basename}" />`
                    : asset.basename;
            }).join(', ');
        }

    },


    watch: {

        /**
         * The components deal with passing around asset objects, however
         * our fieldtype is only concerned with their respective IDs.
         */
        assets() {
            if (this.initializing) return;

            this.update(_.pluck(this.assets, 'id'));
        },

        loading: {
            immediate: true,
            handler(loading) {
                this.$progress.loading(`assets-fieldtype-${this._uid}`, loading);
            }
        }

    },

    mounted() {
        this.displayMode = this.isInsideGridField
            ? 'list'
            : this.config.mode || 'grid';

        this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';

        // We only have URLs in the field data, so we'll need to get the asset data.
        this.initializeAssets();
    }

}
</script>
