<template>
    <element-container @resized="containerWidth = $event.width">
    <div :class="{ 'narrow': containerWidth < 500, 'really-narrow': containerWidth < 280, 'extremely-narrow': containerWidth < 180 }">

        <uploader
            ref="uploader"
            :container="container"
            :enabled="config.allow_uploads"
            :path="folder"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
            @error="uploadError"
        >
            <div slot-scope="{ dragging }" class="assets-fieldtype-drag-container">

                <div class="drag-notification" v-if="config.allow_uploads" v-show="dragging && !showSelector">
                    <svg-icon name="upload" class="h-8 w-8 mr-3" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <div
                    v-if="!isReadOnly && showPicker"
                    class="assets-fieldtype-picker"
                    :class="{
                        'is-expanded': expanded,
                        'bard-drag-handle': isInBardField
                    }"
                >

                    <button
                        type="button"
                        class="btn btn-with-icon"
                        @click="openSelector"
                        @keyup.space.enter="openSelector"
                        tabindex="0">
                        <svg-icon name="folder-image" class="w-6 h-6 text-grey-80"></svg-icon>
                        {{ __('Browse') }}
                    </button>

                    <p class="asset-upload-control text-xs text-grey-60" v-if="config.allow_uploads">
                        <button type="button" class="upload-text-button" @click.prevent="uploadFile">
                            {{ __('Upload file') }}
                        </button>
                        <span v-if="soloAsset" class="drag-drop-text" v-text="__('or drag & drop here to replace.')"></span>
                        <span v-else class="drag-drop-text" v-text="__('or drag & drop here.')"></span>
                    </p>

                    <button
                        type="button"
                        class="delete-bard-set btn btn-icon float-right"
                        v-if="isInBardField"
                        @click.prevent="$dispatch('asset-field.delete-bard-set')">
                        <span class="icon icon-trash"></span>
                    </button>

                </div>

                <uploads
                    v-if="uploads.length"
                    :uploads="uploads"
                />

                <template v-if="expanded">

                    <sortable-list
                        v-if="displayMode === 'grid'"
                        v-model="assets"
                        item-class="asset-tile"
                        handle-class="asset-thumb-container"
                        @dragstart="$emit('focus')"
                        @dragend="$emit('blur')"
                        :constrain-dimensions="true"
                        :disabled="isReadOnly"
                    >
                        <div class="asset-grid-listing border rounded overflow-hidden rounded-t-none" ref="assets">
                            <asset-tile
                                v-for="asset in assets"
                                :key="asset.id"
                                :asset="asset"
                                :is-solo="soloAsset"
                                :read-only="isReadOnly"
                                :show-filename="config.show_filename"
                                @updated="assetUpdated"
                                @removed="assetRemoved"
                                @id-changed="idChanged(asset.id, $event)">
                            </asset-tile>
                        </div>
                    </sortable-list>

                    <div class="asset-table-listing" v-if="displayMode === 'list'">
                        <table class="table-fixed">
                            <sortable-list
                                v-model="assets"
                                item-class="asset-row"
                                handle-class="asset-row"
                                :vertical="true"
                                :constrain-dimensions="true"
                                :disabled="isReadOnly"
                            >
                                <tbody ref="assets">
                                    <tr is="assetRow"
                                        class="asset-row"
                                        v-for="asset in assets"
                                        :key="asset.id"
                                        :asset="asset"
                                        :read-only="isReadOnly"
                                        :show-filename="config.show_filename"
                                        @updated="assetUpdated"
                                        @removed="assetRemoved"
                                        @id-changed="idChanged(asset.id, $event)">
                                    </tr>
                                </tbody>
                            </sortable-list>
                        </table>
                    </div>
                </template>
            </div>
        </uploader>

        <stack v-if="showSelector" name="asset-selector" @closed="closeSelector">
            <selector
                :container="container"
                :folder="folder"
                :restrict-container-navigation="true"
                :restrict-folder-navigation="restrictNavigation"
                :selected="selectedAssets"
                :view-mode="selectorViewMode"
                :max-files="maxFiles"
                @selected="assetsSelected"
                @closed="closeSelector">
            </selector>
        </stack>
    </div>
    </element-container>
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
import AssetRow from './AssetRow.vue';
import AssetTile from './AssetTile.vue';
import Selector from '../../assets/Selector.vue';
import Uploader from '../../assets/Uploader.vue';
import Uploads from '../../assets/Uploads.vue';
import { SortableList } from '../../sortable/Sortable';

export default {

    components: {
        AssetTile,
        AssetRow,
        Selector,
        Uploader,
        Uploads,
        SortableList,
    },


    mixins: [Fieldtype],


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
            displayMode: 'grid',
            containerWidth: null,
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
            return this.config.container || this.meta.container;
        },

        /**
         * The initial folder to be displayed in the selector.
         */
        folder() {
            return this.config.folder || '/';
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
            return clone(this.value);
        },

        /**
         * The IDs of the assets.
         */
        assetIds() {
            return _.pluck(this.assets, 'id');
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
        },

        isInGridField() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (! parent) return false;

                if (parent.grid) {
                    return true;
                }

                vm = parent;
            }
        },

        isInLinkField() {
            let vm = this;

            while (true) {
                let parent = vm.$parent;

                if (! parent) return false;

                if (parent.$options.name === 'link-fieldtype') {
                    return true;
                }

                vm = parent;
            }
        },

        replicatorPreview() {
            return _.map(this.assets, (asset) => {
                return (asset.isImage || asset.isSvg) ?
                    `<img src="${asset.thumbnail}" width="20" height="20" title="${asset.basename}" />`
                    : asset.basename;
            }).join(', ');
        },

        showPicker() {
            if (this.maxFilesReached && ! this.isFullWidth) return false

            if (this.maxFilesReached && (this.isInGridField || this.isInLinkField)) return false

            return true
        },

        isFullWidth() {
            return ! (this.config.width && this.config.width < 100)
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

            this.assets = clone(this.meta.data);
            this.$nextTick(() => {
                this.initializing = false;
                this.loading = false;
            });

            this.$emit('replicator-preview-updated', this.replicatorPreview);
        },

        /**
         * Get asset data from the server
         *
         * Accepts an array of asset URLs and/or IDs.
         */
        loadAssets(assets) {
            if (! assets || ! assets.length) {
                this.loading = false;
                this.assets = [];
                return;
            }

            this.loading = true;

            this.$axios.get(cp_url('assets-fieldtype'), {
                params: { assets }
            }).then(response => {
                this.assets = response.data;
                this.loading = false;
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
         * When an asset is updated in the editor
         */
        assetUpdated(asset) {
            const index = _(this.assets).findIndex({ id: asset.id });
            this.assets.splice(index, 1, asset);
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
            this.uploads = uploads;
        },

        /**
         * When the uploader component encounters an error
         */
        uploadError(upload, uploads) {
            this.uploads = uploads;
            this.$toast.error(upload.errorMessage);
        },

        /**
         * Show the file upload finder window.
         */
        uploadFile() {
            this.$refs.uploader.browse();
        },

        getReplicatorPreviewText() {
            return _.map(this.assets, (asset) => {
                return asset.is_image ?
                    `<img src="${asset.thumbnail}" width="20" height="20" title="${asset.basename}" />`
                    : asset.basename;
            }).join(', ');
        },

        idChanged(oldId, newId) {
            const index = this.value.indexOf(oldId);
            this.update([...this.value.slice(0, index), newId, ...this.value.slice(index + 1)]);
        },

    },


    watch: {

        assets(assets) {
            if (this.initializing) return;

            // The components deal with passing around asset objects, however
            // our fieldtype is only concerned with their respective IDs.
            this.update(this.assetIds);

            this.updateMeta({
                ...this.meta,
                data: [...assets],
            });
        },

        loading(loading) {
            this.$progress.loading(`assets-fieldtype-${this._uid}`, loading);
        },

        value(value) {
            if (_.isEqual(value, this.assetIds)) return;

            this.loadAssets(value);
        },

        showSelector(selecting) {
            this.$emit(selecting ? 'focus' : 'blur');
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
