<template>
    <div
        class="assets-fieldtype"
        :class="{
            'max-files-reached': maxFilesReached,
            'empty': ! assets.length,
            'solo': soloAsset && maxFilesReached
        }"
        @dragover="dragOver"
        @dragleave="dragStop"
        @drop="dragStop">

        <div v-if="loading" class="loading loading-basic">
            <span class="icon icon-circular-graph animation-spin"></span> {{ translate('cp.loading') }}
        </div>

        <div class="drag-notification" v-if="containerSpecified && draggingFile && !showSelector">
            <i class="icon icon-download"></i>
            <h3>{{ translate('cp.drop_to_upload') }}</h3>
        </div>

        <template v-if="!loading">

            <div class="manage-assets" v-if="!maxFilesReached">

                <div v-if="!containerSpecified">
                    <i class="icon icon-warning"></i>
                    {{ translate('cp.no_asset_container_specified') }}
                </div>

                <template v-else>
                    <button
                        type="button"
                        class="btn btn-with-icon mr-8"
                        @click="openSelector"
                        @keyup.space.enter="openSelector"
                        tabindex="0">
                        <span class="icon icon-folder-images"></span>
                        {{ translate('cp.browse_assets') }}
                    </button>

                    <button
                        type="button"
                        class="btn btn-with-icon"
                        @click.prevent="uploadFile">
                        <span class="icon icon-upload-to-cloud"></span>
                        {{ translate('cp.upload') }}
                    </button>

                    <p>{{ translate('cp.or_drag_and_drop_files') }}</p>
                </template>
            </div>

            <uploader
                v-ref:uploader
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
            </uploads>

            <template v-if="expanded && ! soloAsset">

                <div class="asset-grid-listing" v-if="displayMode === 'grid'" v-el:assets>

                    <asset-tile
                        v-for="asset in assets"
                        :asset="asset"
                        @removed="assetRemoved">
                    </asset-tile>

                </div>

                <div class="asset-table-listing" v-if="displayMode === 'list'">

                    <table>
                        <tbody v-el:assets>
                            <tr is="assetRow"
                                v-for="asset in assets"
                                :asset="asset"
                                @removed="assetRemoved">
                            </tr>
                        </tbody>
                    </table>

                </div>

            </template>

            <div class="asset-solo-container" v-if="expanded && soloAsset" v-el:assets>
                <asset-tile
                    v-for="asset in assets"
                    :asset="asset"
                    @removed="assetRemoved">
                </asset-tile>
            </div>
        </template>

        <selector
            v-if="showSelector"
            :container="container"
            :folder="folder"
            :restrict-navigation="restrictNavigation"
            :selected="selectedAssets"
            :view-mode="selectorViewMode"
            :max-files="maxFiles"
            @selected="assetsSelected"
            @closed="closeSelector">
        </selector>
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
            showSelector: false,
            selectorViewMode: null,
            draggingFile: false,
            uploads: [],
            innerDragging: false,
            autoBindChangeWatcher: false,
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
            if (! this.config.max_files) return 0;

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
            // If the value has an :: it's already an ID and we can return as-is.
            // Otherwise, we need to find the ID from the corresponding asset.
            return _(this.data).map((value) => {
                return (value.includes('::')) ? value : _(this.assets).findWhere({ url: value }).id;
            });
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
        }

    },

    events: {
        'close-selector' () {
            this.closeSelector();
        }
    },

    methods: {

        /**
         * Get asset data from the server
         *
         * Accepts an array of asset URLs and/or IDs.
         */
        loadAssets(data) {
            this.loading = true;

            if (! data || ! data.length) {
                this.loading = false;
                this.assets = [];
                return;
            }

            this.$http.post(cp_url('assets/get'), { assets: data }, (response) => {
                this.assets = response;
                this.loading = false;

                this.$nextTick(() => {
                    this.sortable();
                    this.bindChangeWatcher();
                });
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
        },

        /**
         * Close the asset selector modal
         */
        closeSelector() {
            this.showSelector = false;
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
            $(this.$els.assets).sortable({
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
            return _.map(this.data, (asset) => {
                return asset.substring(asset.lastIndexOf('/') + 1);
            }).join(', ');
        }

    },


    watch: {

        /**
         * The components deal with passing around asset objects, however our fieldtype is
         * only concerned with their respective URLs. Note that if the asset belongs to
         * a non-public container, the url property will just be the ID, so we're ok.
         */
        assets(val) {
            this.data = _.pluck(this.assets, 'url');
        }

    },


    ready() {
        this.displayMode = this.isInsideGridField
            ? 'list'
            : this.config.mode || 'grid';

        this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';

        // We only have URLs in the field data, so we'll need to request the asset data from the server.
        this.loadAssets(this.data);
    }

}
</script>
