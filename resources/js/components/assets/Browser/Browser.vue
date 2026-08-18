<template>
    <div ref="browser" class="h-full" @keydown.shift="shiftDown" @keyup="clearShift">
        <Uploader
            ref="internalUploader"
            :container="container.id"
            :path="path"
            :enabled="!uploader && !preventDragging && canUpload"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
            v-slot="{ dragging }"
        >
            <div class="pb-1">
                <div class="drag-notification" v-show="dragging">
                    <Icon name="upload-cloud-large" class="m-4 size-13" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <Listing
                    ref="listing"
                    :url="requestUrl"
                    :columns="columns"
                    :sort-column="sortColumn"
                    :sort-direction="sortDirection"
                    :filters="filters"
                    :action-url="actionUrl"
                    :action-context="actionContext"
                    :allow-bulk-actions="allowBulkActions"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    :preferences-prefix="preferencesPrefix"
                    :additional-parameters="additionalParameters"
                    v-model:search-query="searchQuery"
                    @request-completed="listingRequestCompleted"
                    @update:selections="$emit('selections-updated', $event)"
                >
                    <template #default="{ items }">
                        <slot name="header" v-bind="{ canUpload, openFileBrowser, canCreateFolders, startCreatingFolder, mode, modeChanged }">
                            <Header :title="__(container.title)" icon="assets">
                                <Dropdown v-if="container.can_edit || container.can_delete || container.can_create">
                                    <DropdownMenu>
                                        <DropdownItem
                                            icon="container-add"
                                            v-if="canCreateContainers"
                                            :text="__('Create Container')"
                                            :href="createContainerUrl"
                                        />
                                        <DropdownItem
                                            icon="cog"
                                            v-if="container.can_edit"
                                            :text="__('Configure Container')"
                                            :href="container.edit_url"
                                        />
                                        <DropdownItem
                                            icon="blueprint-edit"
                                            :text="__('Edit Blueprint')"
                                            :href="container.blueprint_url"
                                        />
                                        <DropdownSeparator v-if="container.can_delete" />
                                        <DropdownItem
                                            icon="trash"
                                            variant="destructive"
                                            v-if="container.can_delete"
                                            :text="__('Delete Container')"
                                            @click="$event.preventDefault(); $refs.deleter.confirm()"
                                        />
                                    </DropdownMenu>
                                </Dropdown>

                                <resource-deleter
                                    ref="deleter"
                                    :resource-title="__(container.title)"
                                    :route="container.delete_url"
                                />

                                <Button v-if="canUpload" :text="__('Upload')" icon="upload" @click="openFileBrowser" />
                                <Button v-if="canCreateFolders" :text="__('Create Folder')" icon="folder-add" @click="startCreatingFolder" />

                                <ToggleGroup :model-value="mode" @update:model-value="modeChanged">
                                    <ToggleItem icon="layout-grid" value="grid" />
                                    <ToggleItem icon="layout-list" value="table" />
                                </ToggleGroup>
                            </Header>

                            <div class="flex items-center gap-2 sm:gap-3 py-3 relative overflow-clip st-overflow-clip-margin">
                                <div class="flex flex-1 items-center gap-2 sm:gap-3">
                                    <ListingSearch />
                                    <ListingFilters @filters-updated="filtersUpdated" />
                                </div>
                                <ListingCustomizeColumns v-if="mode === 'table'" />
                            </div>
                        </slot>

                        <div
                            v-if="containerIsEmpty && !creatingFolder"
                            class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 p-6 text-center text-gray-500"
                            v-text="__('No results')"
                        />

                        <Panel v-else :class="{ 'relative overflow-x-auto overscroll-x-contain': mode === 'table' }">
                            <PanelHeader class="flex items-center justify-between gap-2 px-1!">
                                <Breadcrumbs
                                    v-if="!restrictFolderNavigation"
                                    :path="path"
                                    @navigated="selectFolder"
                                />
                                <div v-if="mode === 'grid'" class="flex items-center gap-2 mr-2">
                                    <ui-button
                                        inset
                                        size="sm"
                                        variant="ghost"
                                        icon-only
                                        :icon="checkerboardIcon"
                                        v-tooltip="__('Transparency')"
                                        :aria-label="__('Transparency')"
                                        @click="cycleCheckerboard"
                                    />
                                    <Slider
                                        size="sm"
                                        class="w-24!"
                                        variant="subtle"
                                        v-model="gridThumbnailSize"
                                        :min="60"
                                        :max="300"
                                        :step="25"
                                    />
                                </div>
                            </PanelHeader>

                            <Uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                :allow-selecting-existing="allowSelectingExistingUpload"
                                class="mb-3 rounded-lg"
                                @existing-selected="existingUploadSelected"
                            />

                            <Table
                                ref="table"
                                v-if="mode === 'table'"
                                :assets="items"
                                :folders="folders"
                                :columns="columns"
                                :visible-columns="visibleColumns"
                                :is-searching="isSearching"
                                :selected-assets="selectedAssets"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                            />

                            <Grid
                                ref="grid"
                                v-if="mode === 'grid'"
                                :assets="items"
                                :action-url="actionUrl"
                                :thumbnail-size="gridThumbnailSize"
                                :selected-assets="selectedAssets"
                                :show-checkerboard="showCheckerboard"
                                :checkerboard-mode="checkerboardMode"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                            />

                            <PanelFooter>
                                <ListingPagination />
                            </PanelFooter>
                        </Panel>

                        <slot name="footer" />
                    </template>
                </Listing>
            </div>
        </Uploader>

        <AssetEditor
            v-if="showAssetEditor"
            :id="editedAssetId"
            @previous="editPreviousAsset"
            @next="editNextAsset"
            @closed="closeAssetEditor"
            @saved="assetSaved"
            @action-started="actionStarted"
            @action-completed="actionCompleted"
        />

        <Modal
            :open="showMoveConflictModal"
            :title="__('messages.asset_conflict_title')"
            @update:open="onMoveConflictModalOpenUpdated"
        >
            <p>{{ moveConflictMessage }}</p>

            <template #footer>
                <div class="flex items-center justify-between gap-2 ps-2 pt-3 pb-1">
                    <div>
                        <Checkbox
                            v-if="showMoveConflictApplyToAll"
                            :model-value="moveConflictApplyToAll"
                            :label="__('messages.asset_conflict_apply_to_all')"
                            @update:model-value="moveConflictApplyToAll = $event"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" :text="__('messages.asset_conflict_cancel')" @click="resolveMoveConflict('cancel')" />
                        <Button variant="default" :text="__('messages.asset_conflict_keep_both')" @click="resolveMoveConflict('timestamp')" />
                        <Button variant="danger" :text="__('messages.asset_conflict_overwrite')" @click="resolveMoveConflict('overwrite')" />
                    </div>
                </div>
            </template>
        </Modal>

        <Modal
            :open="showUploadConflictModal"
            :title="__('messages.asset_conflict_title')"
            @update:open="onUploadConflictModalOpenUpdated"
        >
            <p>{{ uploadConflictMessage }}</p>

            <template #footer>
                <div class="flex items-center justify-between gap-2 ps-2 pt-3 pb-1">
                    <div>
                        <Checkbox
                            v-if="showUploadConflictApplyToAll"
                            :model-value="uploadConflictApplyToAll"
                            :label="__('messages.asset_conflict_apply_to_all')"
                            @update:model-value="uploadConflictApplyToAll = $event"
                        />
                    </div>
                    <div class="flex items-center gap-2">
                        <Button variant="ghost" :text="__('messages.asset_conflict_cancel')" @click="resolveUploadConflict('cancel')" />
                        <Button variant="default" :text="__('messages.asset_conflict_keep_both')" @click="resolveUploadConflict('timestamp')" />
                        <Button variant="danger" :text="__('messages.asset_conflict_overwrite')" @click="resolveUploadConflict('overwrite')" />
                    </div>
                </div>
            </template>
        </Modal>
    </div>
</template>

<script>
import AssetThumbnail from './Thumbnail.vue';
import AssetEditor from '../Editor/Editor.vue';
import Grid from './Grid.vue';
import Table from './Table.vue';
import HasPreferences from '../../data-list/HasPreferences';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';
import { debounce, sortBy } from 'lodash-es';
import {
    Header,
    Button,
    ButtonGroup,
    Dropdown,
    DropdownSeparator,
    DropdownItem,
    DropdownMenu,
    Panel,
    PanelHeader,
    PanelFooter,
    Listing,
    ListingTable,
    ListingPagination,
    ListingFilters,
    ListingSearch,
    ListingCustomizeColumns,
    Slider,
    Icon,
    ToggleGroup,
    ToggleItem,
    Modal,
    Checkbox,
} from '@ui';
import Breadcrumbs from './Breadcrumbs.vue';
import useCheckerboard from '@/composables/checkerboard.js';

export default {
    mixins: [HasPreferences],

    components: {
        PanelFooter,
        Panel,
        PanelHeader,
        DropdownMenu,
        DropdownItem,
        Dropdown,
        DropdownSeparator,
        AssetThumbnail,
        AssetEditor,
        Uploader,
        Uploads,
        Grid,
        Table,
        Header,
        Button,
        ButtonGroup,
        Listing,
        ListingTable,
        ListingPagination,
        ListingSearch,
        ListingFilters,
        ListingCustomizeColumns,
        Breadcrumbs,
        Slider,
        Icon,
        ToggleGroup,
        ToggleItem,
        Modal,
        Checkbox,
    },

    props: {
        allowBulkActions: {
            type: Boolean,
            default: true,
        },
        allowSelectingExistingUpload: Boolean,
        autoselectUploads: Boolean,
        canCreateContainers: Boolean,
        createContainerUrl: String,
        container: Object,
        initialEditingAssetId: String,
        maxFiles: Number,
        queryScopes: Array,
        restrictFolderNavigation: Boolean, // Whether to restrict to a single folder and prevent navigation.
        selectedAssets: Array,
        selectedPath: String, // The path to display, determined by a parent component.
        filters: Array,
        initialColumns: {
            type: Array,
            default: () => [],
        },
        uploader: {
            type: Object,
            default: null,
        },
    },

    setup() {
        const checkerboard = useCheckerboard();
        return {
            showCheckerboard: checkerboard.enabled,
            checkerboardIcon: checkerboard.icon,
            checkerboardMode: checkerboard.mode,
            cycleCheckerboard: checkerboard.cycle,
        };
    },

    data() {
        return {
            columns: this.initialColumns,
            visibleColumns: this.initialColumns.filter(column => column.visible),
            containers: [],
            initializing: true,
            loading: true,
            assets: [],
            path: this.selectedPath,
            folders: [],
            folder: {},
            searchQuery: '',
            activeFilters: {},
            editedAssetId: this.initialEditingAssetId,
            creatingFolder: false,
            creatingFolderError: false,
            uploads: [],
            uploadConflictPolicy: null,
            uploadConflictApplyToAll: false,
            uploadConflictUploadId: null,
            uploadConflictMessage: '',
            showUploadConflictModal: false,
            uploadConflictQueue: [],
            moveConflictContext: null,
            moveConflictMessage: '',
            showMoveConflictModal: false,
            moveConflictApplyToAll: false,
            moveConflictPolicy: null,
            page: 1,
            preferencesPrefix: `assets.${this.container.id}`,
            meta: {},
            sortColumn: this.container.sort_field,
            sortDirection: this.container.sort_direction,
            mode: 'table',
            actionUrl: null,
            folderActionUrl: null,
            shifting: false,
            lastItemClicked: null,
            preventDragging: false,
            gridThumbnailSize: this.$preferences.get('assets.browser_thumbnail_size', 200),
        };
    },

    computed: {
        requestUrl() {
            return this.isSearching
                ? cp_url(
                      `assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ''}`,
                  ).replace(/\/$/, '')
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`).replace(/\/$/, '');
        },

        actionContext() {
            return { container: this.container.id };
        },

        additionalParameters() {
            return {
                queryScopes: this.queryScopes,
            };
        },

        canCreateFolders() {
            return this.folder && this.container.can_create_folders && !this.restrictFolderNavigation;
        },

        canUpload() {
            return this.folder && this.container.can_upload;
        },

        containerIsEmpty() {
            return this.assets.length === 0 && this.folders.length === 0 && (!this.folder || !this.folder.parent_path);
        },

        editedAssetBasename() {
            let asset = this.assets.find((asset) => asset.id == this.editedAssetId);

            return asset ? asset.basename : null;
        },

        hasMaxFiles() {
            return this.maxFiles !== undefined && this.maxFiles !== Infinity;
        },

        hasSelections() {
            return this.selectedAssets.length > 0;
        },

        hasActiveFilters() {
            return Object.entries(this.activeFilters).some(([key, value]) => {
                if (Array.isArray(value)) {
                    return value.length > 0;
                } else if (typeof value === 'object' && value !== null) {
                    return Object.keys(value).length > 0;
                }
                return Boolean(value);
            });
        },

        isSearching() {
            return this.searchQuery || this.hasActiveFilters;
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
            };
        },

        visibleColumnParameters: {
            get() {
                if (this.visibleColumns === null || this.visibleColumns === undefined) {
                    return null;
                }

                return this.visibleColumns.map(column => column.field).join(',');
            },
            set(value) {
                this.visibleColumns = value.split(',').map(field => this.columns.find(column => column.field === field));
            },
        },

        reachedSelectionLimit() {
            return this.selectedAssets.length >= this.maxFiles;
        },

        showAssetEditor() {
            return Boolean(this.editedAssetId);
        },

        sharedAssetProps() {
            return {
                actionUrl: this.actionUrl,
                containerIsEmpty: this.containerIsEmpty,
                folder: this.folder,
                folderActionUrl: this.folderActionUrl,
                folders: this.folders,
                maxFiles: this.maxFiles,
                restrictFolderNavigation: this.restrictFolderNavigation,
                path: this.path,
                creatingFolder: this.creatingFolder,
                creatingFolderError: this.creatingFolderError,
            };
        },

        sharedAssetEvents() {
            return {
                'action-completed': this.actionCompleted,
                'action-started': this.actionStarted,
                'edit': this.edit,
                'edit-asset': (event) => this.$emit('edit-asset', event),
                'select-folder': this.selectFolder,
                'create-folder': this.createFolder,
                'cancel-creating-folder': () => {
                    this.creatingFolder = false;
                    this.creatingFolderError = false;
                },
                'asset-move-conflict': this.openMoveConflictModal,
                'prevent-dragging': (preventDragging) => (this.preventDragging = preventDragging),
                'update:creatingFolderError': (value) => (this.creatingFolderError = value),
            };
        },

        showMoveConflictApplyToAll() {
            return (this.moveConflictContext?.pendingSelections?.length || 0) > 1;
        },

        showUploadConflictApplyToAll() {
            return this.uploads.filter((upload) => upload.errorStatus === 409).length > 1;
        },
    },

    mounted() {
        this.mode = this.getPreference('mode') || 'table';

        this.addToCommandPalette();
    },

    watch: {
        mode(mode) {
            this.setPreference('mode', mode == 'table' ? null : mode);
        },

        initializing(initializing) {
              if (initializing === false) {
                  this.$emit('initialized');
              }
        },

        editedAssetId(editedAssetId) {
            let path = editedAssetId
                ? [this.path, this.editedAssetBasename].filter((value) => value != '/').join('/') + '/edit'
                : this.path;

            this.$emit('navigated', path);
        },

        loading(loading) {
            this.$progress.loading('asset-browser', loading);
        },

        parameters(after, before) {
            if (this.initializing || JSON.stringify(before) === JSON.stringify(after)) return;
            this.loadAssets();
        },

        path(path) {
            this.loadAssets();
            this.$emit('path-changed', path);
        },

        searchQuery() {
            this.page = 1;
        },

        activeFilters: {
            deep: true,
            handler() {
                this.page = 1;
            },
        },

        selectedPath: {
            immediate: true,
            handler(newPath) {
                if (!newPath.endsWith('/edit')) {
                    this.path = newPath;
                }
            },
        },

        gridThumbnailSize: {
            handler: debounce(function (size) {
                this.$preferences.set('assets.browser_thumbnail_size', size);
            }, 300),
        },
    },

    methods: {
        onMoveConflictModalOpenUpdated(open) {
            if (open) {
                this.showMoveConflictModal = true;
                return;
            }

            if (!this.showMoveConflictModal) {
                return;
            }

            this.resolveMoveConflict('cancel');
        },

        onUploadConflictModalOpenUpdated(open) {
            if (open) {
                this.showUploadConflictModal = true;
                return;
            }

            if (!this.showUploadConflictModal) {
                return;
            }

            this.resolveUploadConflict('cancel');
        },

        filtersUpdated(filters) {
            this.activeFilters = filters;
        },

        modeChanged(mode) {
            this.mode = mode;
        },

        startCreatingFolder() {
            this.creatingFolder = true;
            this.creatingFolderError = false;
        },

        listingRequestCompleted({ response }) {
            this.assets = response.data.data;

            if (this.isSearching) {
                this.folder = null;
                this.folders = [];
            } else {
                const { meta, links } = response.data;
                this.folder = meta.folder;
                this.folders = meta.folder.folders;
                this.actionUrl = links.asset_action;
                this.folderActionUrl = links.folder_action;
            }

            this.initializing = false;
            this.loading = false;
        },

        actionStarted() {
            this.loading = true;
        },

        actionCompleted(successful = null, response = {}) {
            if (successful === true && response.message !== false) {
                this.$toast.success(response.message || __('Action completed'));
            } else if (successful === false) {
                this.$toast.error(response.message || __('Action failed'));
            }

            // Intentionally not completing the loading state here since
            // the listing will refresh and immediately restart it.
            // this.loading = false;

            this.$refs.listing.refresh();
        },

        assetSaved() {
            this.loadAssets();
        },

        clearShift() {
            this.shifting = false;
        },

        async editPreviousAsset() {
            let currentAssetIndex = this.assets.findIndex((asset) => asset.id === this.editedAssetId);

            // When we're editing the first asset on the page, navigating to the previous asset
            // requires us to load the previous page of assets, if there is one.
            if (currentAssetIndex === 0) {
                if (this.page > 1) {
                    this.page = this.page - 1;
                    await this.loadAssets();

                    if (this.assets.length > 0) {
                        this.editedAssetId = null;

                        this.$nextTick(() => {
                            this.editedAssetId = this.assets.slice(-1)[0].id;
                        });
                    }
                }

                this.editedAssetId = null;
                return;
            }

            this.editedAssetId = null;

            this.$nextTick(() => {
                this.editedAssetId = this.assets.slice(currentAssetIndex - 1, currentAssetIndex)[0].id;
            });
        },

        async editNextAsset() {
            let currentAssetIndex = this.assets.findIndex((asset) => asset.id === this.editedAssetId);

            // When we're editing the last asset on the page, navigating to the next asset
            // requires us to load the next page of assets, if there is one.
            if (currentAssetIndex === this.assets.length - 1) {
                if (this.meta.last_page > this.page) {
                    this.page = this.page + 1;
                    await this.loadAssets();

                    if (this.assets.length > 0) {
                        this.editedAssetId = null;

                        this.$nextTick(() => {
                            this.editedAssetId = this.assets[0].id;
                        });
                    }
                }

                this.editedAssetId = null;
                return;
            }

            this.editedAssetId = null;

            this.$nextTick(() => {
                this.editedAssetId = this.assets.slice(currentAssetIndex + 1, currentAssetIndex + 2)[0].id;
            });
        },

        closeAssetEditor() {
            this.editedAssetId = null;
        },

        createFolder(name) {
            this.$axios
                .post(cp_url(`asset-containers/${this.container.id}/folders`), { path: this.path, directory: name })
                .then((response) => {
                    this.$toast.success(__('Folder created'));

                    this.folders.push(response.data);
                    this.folders = sortBy(this.folders, 'title');
                    this.creatingFolder = false;
                    this.creatingFolderError = false;

                    this.$refs.grid?.clearNewFolderName();
                    this.$refs.table?.clearNewFolderName();
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;

                        errors.directory
                            ? this.$toast.error(errors.directory[0])
                            : this.$toast.error(message);

                        this.creatingFolderError = true;
                        this.$refs.grid?.focusNewFolderInput();
                        this.$refs.table?.focusNewFolderInput();
                    } else {
                        this.$toast.error(__('Something went wrong'));
                        this.creatingFolderError = true;
                    }
                });
        },

        edit(id) {
            this.editedAssetId = id;
        },

        existingUploadSelected(upload) {
            const path = `${this.folder.path}/${upload.basename}`.replace(/^\/+/, '');
            const id = `${this.container.id}::${path}`;

            this.selectedAssets.push(id);
            this.$emit('selections-updated', this.selectedAssets);
        },

        folderActions(folder) {
            return folder.actions || this.folder.actions || [];
        },

        loadAssets() {
            this.$nextTick(() => this.$refs.listing.refresh());
        },

        openFileBrowser() {
            (this.uploader || this.$refs.internalUploader).browse();
        },

        selectFolder(path) {
            // Trigger re-loading of assets in the selected folder.
            this.path = path;
            this.page = 1;

            this.$emit('navigated', this.path);
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

        sorted(column, direction) {
            this.sortColumn = column;
            this.sortDirection = direction;
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

        uploadCompleted(asset, uploads, upload) {
            if (['overwrite', 'timestamp'].includes(upload?.resolution)) {
                const urls = this.getUploadConflictCacheBustUrls(upload);

                if (urls.length) {
                    Statamic.$callbacks.call('bustAndReloadImageCaches', urls);
                }
            }

            if (this.autoselectUploads) {
                this.sortColumn = 'last_modified';
                this.sortDirection = 'desc';

                if (this.maxFiles === 1) {
                    this.selectedAssets.splice(0, this.selectedAssets.length, asset.id);
                } else if (!this.reachedSelectionLimit) {
                    this.selectedAssets.push(asset.id);
                }
                this.$emit('selections-updated', this.selectedAssets);
            }

            this.loadAssets();
            this.$toast.success(__(':file uploaded', { file: asset.basename }));
        },

        uploadError(upload, uploads) {
            this.uploads = uploads;

            if (upload.errorStatus !== 409) {
                this.$toast.error(upload.errorMessage);
                return;
            }

            if (this.uploadConflictPolicy) {
                this.applyUploadConflict(upload, this.uploadConflictPolicy);
                return;
            }

            this.enqueueUploadConflict(upload.id);
            this.openNextUploadConflictFromQueue();
        },

        uploadsUpdated(uploads) {
            this.uploads = uploads;

            if (uploads.length === 0) {
                this.uploadConflictPolicy = null;
                this.uploadConflictApplyToAll = false;
                this.uploadConflictQueue = [];
                this.uploadConflictUploadId = null;
                this.showUploadConflictModal = false;
            } else {
                const uploadIds = new Set(uploads.map((upload) => upload.id));
                this.uploadConflictQueue = this.uploadConflictQueue.filter((id) => uploadIds.has(id));
            }
        },

        getUploadById(id) {
            return this.uploads.find((upload) => upload.id === id);
        },

        openUploadConflictModal(upload) {
            this.uploadConflictUploadId = upload.id;
            this.uploadConflictMessage = __('messages.asset_upload_conflict_message', {
                filename: upload.basename,
            });
            this.showUploadConflictModal = true;
        },

        resolveUploadConflict(strategy) {
            const currentConflictUploadId = this.uploadConflictUploadId;
            const upload = this.getUploadById(this.uploadConflictUploadId);

            if (this.uploadConflictApplyToAll) {
                this.uploadConflictPolicy = strategy;

                this.uploads
                    .filter((item) => item.errorStatus === 409)
                    .forEach((item) => this.applyUploadConflict(item, strategy));

                this.uploadConflictQueue = [];
                this.uploadConflictUploadId = null;
                this.uploadConflictMessage = '';
                this.showUploadConflictModal = false;
            } else if (upload) {
                this.applyUploadConflict(upload, strategy);
                this.dequeueUploadConflict(currentConflictUploadId);
                this.uploadConflictUploadId = null;
                this.uploadConflictMessage = '';

                // Keep the same modal open and dynamically swap to the next conflict.
                const hasNextConflict = this.openNextUploadConflictFromQueue();

                if (!hasNextConflict) {
                    this.showUploadConflictModal = false;
                }
            } else {
                this.showUploadConflictModal = false;
            }

            this.uploadConflictApplyToAll = false;
        },

        applyUploadConflict(upload, strategy) {
            if (strategy === 'cancel') {
                upload.skip();
                return;
            }

            upload.retry({
                option: strategy,
            }, {
                conflict: upload.conflict,
                resolution: strategy,
            });
        },

        getUploadConflictCacheBustUrls(upload) {
            if (upload?.conflict?.existing) {
                return [upload.conflict.existing.preview, upload.conflict.existing.thumbnail].filter(Boolean);
            }

            const folderPath = (this.folder?.path || '').replace(/^\/+|\/+$/g, '');
            const basename = upload?.basename || '';
            const fullPath = [folderPath, basename].filter(Boolean).join('/');
            const existingAsset = this.assets.find((asset) => {
                if (asset.path === fullPath) {
                    return true;
                }

                return folderPath === '' && asset.basename === basename;
            });

            if (!existingAsset) {
                return [];
            }

            return [existingAsset.preview, existingAsset.thumbnail].filter(Boolean);
        },

        enqueueUploadConflict(id) {
            if (!id || this.uploadConflictQueue.includes(id)) {
                return;
            }

            this.uploadConflictQueue.push(id);
        },

        dequeueUploadConflict(id) {
            if (!id) {
                return;
            }

            this.uploadConflictQueue = this.uploadConflictQueue.filter((queuedId) => queuedId !== id);
        },

        openNextUploadConflictFromQueue() {
            if (this.showUploadConflictModal || this.uploadConflictPolicy) {
                if (!this.uploadConflictUploadId) {
                    // Modal is visible but no active conflict selected yet.
                    // Continue to resolve the next queued conflict.
                } else {
                    return false;
                }
            }

            while (this.uploadConflictQueue.length > 0) {
                const nextConflictId = this.uploadConflictQueue[0];
                const nextConflict = this.getUploadById(nextConflictId);

                if (!nextConflict || nextConflict.errorStatus !== 409) {
                    this.uploadConflictQueue.shift();
                    continue;
                }

                this.openUploadConflictModal(nextConflict);
                return true;
            }

            return false;
        },

        openMoveConflictModal({ action, asset, destinationFolder, selections, message, conflict, completedMoves }) {
            const initialRemap =
                completedMoves && typeof completedMoves === 'object' && !Array.isArray(completedMoves) ? completedMoves : {};
            const completedSelectionIds = new Set(Object.keys(initialRemap).map((id) => String(id)));
            const pendingSelections = Array.from(new Set((selections || [asset?.id]).filter(Boolean))).filter(
                (id) => !completedSelectionIds.has(String(id)),
            );

            this.moveConflictContext = {
                action,
                asset,
                destinationFolder,
                pendingSelections,
                conflict,
                idRemap: { ...initialRemap },
            };
            this.moveConflictMessage = message;
            this.showMoveConflictModal = true;
        },

        remapMoveConflictAssetId(id, idRemap = {}) {
            let current = id;
            const seen = new Set();

            while (current && idRemap[current] && !seen.has(current)) {
                seen.add(current);
                current = idRemap[current];
            }

            return current;
        },

        async resolveMoveConflict(strategy) {
            const conflictContext = this.moveConflictContext;
            this.showMoveConflictModal = false;
            this.moveConflictContext = null;

            if (!conflictContext) {
                return;
            }

            if (this.moveConflictApplyToAll) {
                this.moveConflictPolicy = strategy;
            }

            this.moveConflictApplyToAll = false;
            this.actionStarted();
            await this.continueMoveConflictResolution(conflictContext, strategy);
        },

        async continueMoveConflictResolution(context, strategy = null) {
            let nextStrategy = strategy;

            while (true) {
                const conflictAssetId = context.conflict?.asset?.id;
                const resolution = this.moveConflictPolicy;

                if (conflictAssetId) {
                    const idRemap = context.idRemap || {};

                    context.pendingSelections = context.pendingSelections.filter(
                        (id) => this.remapMoveConflictAssetId(id, idRemap) !== conflictAssetId,
                    );
                }

                if (nextStrategy && nextStrategy !== 'cancel' && conflictAssetId) {
                    const resolutionResult = await this.runMoveConflictAction(
                        context,
                        [this.remapMoveConflictAssetId(conflictAssetId, context.idRemap || {})],
                        nextStrategy,
                    );

                    if (resolutionResult.success === false) {
                        this.moveConflictPolicy = null;
                        this.actionCompleted(false, resolutionResult);
                        return;
                    }

                    if (nextStrategy === 'overwrite') {
                        const urls = [
                            context.conflict?.existing?.preview,
                            context.conflict?.existing?.thumbnail,
                        ].filter(Boolean);

                        if (urls.length) {
                            Statamic.$callbacks.call('bustAndReloadImageCaches', urls);
                        }
                    }
                }

                if (context.pendingSelections.length === 0) {
                    this.moveConflictPolicy = null;
                    this.actionCompleted(true, {
                        message: false,
                    });
                    return;
                }

                const response = await this.runMoveConflictAction(context, context.pendingSelections, null);

                if (response.success !== false) {
                    this.moveConflictPolicy = null;
                    this.actionCompleted(true, response);
                    return;
                }

                if (response.conflict?.type !== 'asset_move') {
                    this.moveConflictPolicy = null;
                    this.actionCompleted(false, response);
                    return;
                }

                if (
                    response.completed_moves &&
                    typeof response.completed_moves === 'object' &&
                    !Array.isArray(response.completed_moves) &&
                    Object.keys(response.completed_moves).length
                ) {
                    context.idRemap = { ...context.idRemap, ...response.completed_moves };
                }

                context.conflict = response.conflict;
                this.moveConflictMessage = response.message;

                const currentConflictAssetId = response.conflict?.asset?.id;

                if (!currentConflictAssetId) {
                    this.moveConflictPolicy = null;
                    this.actionCompleted(false, response);
                    return;
                }

                if (resolution) {
                    nextStrategy = resolution;
                    continue;
                }

                this.moveConflictContext = context;
                this.showMoveConflictModal = true;
                return;
            }
        },

        async runMoveConflictAction(context, selections, strategy = null) {
            const idRemap = context.idRemap || {};
            const selectedAssetIds = Array.from(
                new Set(
                    (selections || [])
                        .filter(Boolean)
                        .map((id) => this.remapMoveConflictAssetId(id, idRemap)),
                ),
            );

            if (selectedAssetIds.length === 0) {
                return {
                    success: true,
                    message: false,
                };
            }

            const payload = {
                action: context.action.handle,
                context: {
                    ...context.action.context,
                    ...(strategy ? { conflict: strategy } : {}),
                },
                selections: selectedAssetIds,
                values: {
                    folder: context.destinationFolder.path,
                },
            };

            try {
                const { data } = await this.$axios.post(this.actionUrl, payload);

                return data || {};
            } catch ({ response }) {
                return response?.data || {
                    success: false,
                    message: __('Action failed'),
                };
            }
        },

        addToCommandPalette() {
            Statamic.$commandPalette.add({
                when: () => this.canUpload,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Upload'),
                icon: 'upload',
                action: () => this.openFileBrowser(),
                prioritize: true,
            });

            Statamic.$commandPalette.add({
                when: () => this.canCreateFolders,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Create Folder'),
                icon: 'folder-add',
                action: () => this.startCreatingFolder(),
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Toggle Grid Layout'),
                icon: 'layout-grid',
                when: () => this.mode === 'table',
                action: () => this.mode = 'grid',
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Toggle List Layout'),
                icon: 'layout-list',
                when: () => this.mode === 'grid',
                action: () => this.mode = 'table',
            });

            if (this.createContainerUrl) {
                Statamic.$commandPalette.add({
                    when: () => this.canCreateContainers,
                    category: Statamic.$commandPalette.category.Actions,
                    text: __('Create Container'),
                    icon: 'container-add',
                    url: this.createContainerUrl,
                });
            }

            Statamic.$commandPalette.add({
                when: () => this.container.can_edit,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Configure Container'),
                icon: 'cog',
                url: this.container.edit_url,
            });

            Statamic.$commandPalette.add({
                category: Statamic.$commandPalette.category.Actions,
                text: __('Edit Blueprint'),
                icon: 'blueprint-edit',
                url: this.container.blueprint_url,
            });

            Statamic.$commandPalette.add({
                when: () => this.container.can_delete,
                category: Statamic.$commandPalette.category.Actions,
                text: __('Delete Container'),
                icon: 'trash',
                action: () => this.$refs.deleter.confirm(),
            });
        }
    },
};
</script>
