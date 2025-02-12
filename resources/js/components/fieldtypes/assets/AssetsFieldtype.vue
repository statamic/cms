<template>
    <div class="@container">
        <div
            v-if="hasPendingDynamicFolder"
            class="w-full rounded-md border border-dashed px-4 py-3 text-sm text-gray-700 dark:border-dark-200 dark:text-dark-175"
            v-html="pendingText"
        />

        <uploader
            ref="uploader"
            :container="container"
            :enabled="canUpload"
            :path="folder"
            @updated="uploadsUpdated"
            @upload-complete="uploadComplete"
            @error="uploadError"
            v-slot="{ dragging }"
        >
            <div class="assets-fieldtype-drag-container">
                <div class="drag-notification" v-if="config.allow_uploads" v-show="dragging && !showSelector">
                    <svg-icon name="upload" class="h-6 w-6 @md:mr-6 @md:h-8 @md:w-8 ltr:mr-2 rtl:ml-2" />
                    <span>{{ __('Drop to Upload') }}</span>
                </div>

                <div
                    v-if="!isReadOnly && showPicker"
                    class="assets-fieldtype-picker space-x-4"
                    :class="{
                        'is-expanded': expanded,
                        'bard-drag-handle': isInBardField,
                    }"
                >
                    <button
                        v-if="canBrowse"
                        :class="{ 'opacity-0': dragging }"
                        type="button"
                        class="btn btn-with-icon"
                        @click="openSelector"
                        @keyup.space.enter="openSelector"
                        tabindex="0"
                    >
                        <svg-icon name="folder-image" class="h-4 w-4 text-gray-800 dark:text-dark-150"></svg-icon>
                        {{ __('Browse') }}
                    </button>
                    <p class="asset-upload-control flex-1" v-if="canUpload">
                        <button type="button" class="upload-text-button" @click.prevent="uploadFile">
                            {{ __('Upload file') }}
                        </button>
                        <span
                            v-if="soloAsset"
                            class="drag-drop-text"
                            v-text="__('or drag & drop here to replace.')"
                        ></span>
                        <span v-else class="drag-drop-text" v-text="__('or drag & drop here.')"></span>
                    </p>
                    <dropdown-list v-if="meta.rename_folder">
                        <data-list-inline-actions
                            :item="folder"
                            :url="meta.rename_folder.url"
                            :actions="[meta.rename_folder.action]"
                            @completed="renameFolderActionCompleted"
                        />
                    </dropdown-list>
                </div>

                <uploads
                    v-if="uploads.length"
                    :uploads="uploads"
                    allow-selecting-existing
                    @existing-selected="uploadSelected"
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
                        :distance="5"
                        :animate="false"
                        append-to="body"
                    >
                        <div
                            class="asset-grid-listing overflow-hidden rounded border dark:border-dark-900"
                            :class="{ 'rounded-t-none': !isReadOnly && (showPicker || uploads.length) }"
                            ref="assets"
                        >
                            <asset-tile
                                v-for="asset in assets"
                                :key="asset.id"
                                :asset="asset"
                                :read-only="isReadOnly"
                                :show-filename="config.show_filename"
                                :show-set-alt="showSetAlt"
                                @updated="assetUpdated"
                                @removed="assetRemoved"
                                @id-changed="idChanged(asset.id, $event)"
                            >
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
                                :disabled="isReadOnly"
                                :distance="5"
                                :mirror="false"
                            >
                                <tbody ref="assets">
                                    <component
                                        is="assetRow"
                                        class="asset-row"
                                        v-for="asset in assets"
                                        :key="asset.id"
                                        :asset="asset"
                                        :read-only="isReadOnly"
                                        :show-filename="config.show_filename"
                                        :show-set-alt="showSetAlt"
                                        @updated="assetUpdated"
                                        @removed="assetRemoved"
                                        @id-changed="idChanged(asset.id, $event)"
                                    />
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
                :restrict-folder-navigation="restrictNavigation"
                :selected="selectedAssets"
                :view-mode="selectorViewMode"
                :max-files="maxFiles"
                :query-scopes="queryScopes"
                @selected="assetsSelected"
                @closed="closeSelector"
            >
            </selector>
        </stack>
    </div>
</template>

<style>
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
import Fieldtype from '../Fieldtype.vue';
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

    inject: {
        store: { default: null },
        isInBardField: {
            name: 'isInBardField',
            default: false,
        },
        isInGridField: {
            name: 'isInGridField',
            default: false,
        },
        isInLinkField: {
            name: 'isInLinkField',
            default: false,
        },
    },

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
            lockedDynamicFolder: this.meta.dynamicFolder,
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
            let folder = this.configuredFolder;

            if (this.isUsingDynamicFolder) {
                folder = folder + '/' + (this.lockedDynamicFolder || this.dynamicFolder);
            }

            folder = folder.replace(/^\/+/, '');

            return folder === '' ? '/' : folder;
        },

        configuredFolder() {
            return this.config.folder || '/';
        },

        isUsingDynamicFolder() {
            return !!this.config.dynamic;
        },

        hasPendingDynamicFolder() {
            return this.isUsingDynamicFolder && !this.lockedDynamicFolder && !this.dynamicFolder;
        },

        dynamicFolder() {
            const field = this.config.dynamic;
            if (!['id', 'slug', 'author'].includes(field)) {
                throw new Error(`Dynamic folder field [${field}] is invalid. Must be one of: id, slug, author`);
            }

            const value = this.store.values[field];

            // If value is an array (e.g. a users fieldtype), get the first item.
            return Array.isArray(value) ? value[0] : value;
        },

        /**
         * Whether assets should be restricted to the specified container
         * and folder. This will prevent navigation to other places.
         */
        restrictNavigation() {
            return this.isUsingDynamicFolder || this.config.restrict || false;
        },

        /**
         * The maximum number of files allowed.
         */
        maxFiles() {
            if (!this.config.max_files) return Infinity;

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

        /**
         * The scopes to use to filter the queries.
         */
        queryScopes() {
            return this.config.query_scopes || [];
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return replicatorPreviewHtml(
                _.map(this.assets, (asset) => {
                    return asset.isImage || asset.isSvg
                        ? `<img src="${asset.thumbnail}" width="20" class="max-w-5 max-h-5" height="20" title="${asset.basename}" />`
                        : asset.basename;
                }).join(', '),
            );
        },

        showPicker() {
            if (!this.canBrowse && !this.canUpload) return false;

            if (this.maxFilesReached && !this.isFullWidth) return false;

            if (this.maxFilesReached && (this.isInGridField || this.isInLinkField)) return false;

            return true;
        },

        isFullWidth() {
            return !(this.config.width && this.config.width < 100);
        },

        showSetAlt() {
            return this.config.show_set_alt && !this.isReadOnly;
        },

        canBrowse() {
            const hasPermission =
                this.can('configure asset containers') || this.can('view ' + this.container + ' assets');

            if (!hasPermission) return false;

            return !this.hasPendingDynamicFolder;
        },

        canUpload() {
            const hasPermission =
                this.config.allow_uploads &&
                (this.can('configure asset containers') || this.can('upload ' + this.container + ' assets'));

            if (!hasPermission) return false;

            return !this.hasPendingDynamicFolder;
        },

        pendingText() {
            return this.config.dynamic === 'id'
                ? __('statamic::fieldtypes.assets.dynamic_folder_pending_save')
                : __('statamic::fieldtypes.assets.dynamic_folder_pending_field', {
                      field: `<code>${this.config.dynamic}</code>`,
                  });
        },
    },

    events: {
        'close-selector'() {
            this.closeSelector();
        },
    },

    methods: {
        initializeAssets() {
            if (!this.meta.data) {
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
            if (!assets || !assets.length) {
                this.loading = false;
                this.assets = [];
                return;
            }

            this.loading = true;

            this.$axios
                .post(cp_url('assets-fieldtype'), {
                    assets,
                })
                .then((response) => {
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
            this.lockDynamicFolder();
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

            this.lockDynamicFolder();
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

        idChanged(oldId, newId) {
            const index = this.value.indexOf(oldId);
            this.update([...this.value.slice(0, index), newId, ...this.value.slice(index + 1)]);
        },

        lockDynamicFolder() {
            if (this.isUsingDynamicFolder && !this.lockedDynamicFolder) this.lockedDynamicFolder = this.dynamicFolder;
        },

        syncDynamicFolderFromValue(value) {
            if (!this.isUsingDynamicFolder) return;

            this.lockedDynamicFolder = null;

            if (value.length === 0) {
                // If there are no assets, we should get the dynamic folder naturally.
                this.lockDynamicFolder();
            } else {
                // Otherwise, figure it out from the first selected asset.
                const first = value[0];
                const segments = first.split('::')[1].split('/');
                this.lockedDynamicFolder = segments[segments.length - 2];
            }

            // Set the new folder in the rename action.
            const meta = this.meta;
            meta.rename_folder.action.context.folder = this.folder;
            this.updateMeta(meta);
        },

        renameFolderActionCompleted(successful = null, response = {}) {
            if (successful === false) return;

            this.$events.$emit('reset-action-modals');

            if (response.message !== false) {
                this.$toast.success(response.message || __('Action completed'));
            }

            // Update the folder in the current asset values.
            // They will be adjusted in the content but not here automatically since there's no refresh.
            const newFolder = response[0].path;
            this.update(this.value.map((id) => id.replace(`::${this.folder}`, `::${newFolder}`)));
            this.lockedDynamicFolder = this.configuredFolder
                ? newFolder.replace(`${this.configuredFolder}/`, '')
                : newFolder;
        },

        uploadSelected(upload) {
            const path = `${this.folder}/${upload.basename}`.replace(/^\/+/, '');
            const id = `${this.container}::${path}`;

            this.uploads.splice(this.uploads.indexOf(upload), 1);

            if (this.value.includes(id)) return;

            if (this.maxFiles === 1) {
                this.loadAssets([id]);
            } else {
                this.loadAssets([...this.value, id]);
            }
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
            this.$progress.loading(`assets-fieldtype-${this.$.uid}`, loading);
        },

        value(value) {
            if (_.isEqual(value, this.assetIds)) return;

            this.syncDynamicFolderFromValue(value);

            this.loadAssets(value);
        },

        showSelector(selecting) {
            this.$emit(selecting ? 'focus' : 'blur');
        },
    },

    mounted() {
        this.displayMode = this.isInsideGridField ? 'list' : this.config.mode || 'grid';

        this.selectorViewMode = Cookies.get('statamic.assets.listing_view_mode') || 'grid';

        // We only have URLs in the field data, so we'll need to get the asset data.
        this.initializeAssets();
    },
};
</script>
