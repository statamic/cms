<template>
    <div ref="browser" @keydown.shift="shiftDown" @keyup="clearShift">
        <Uploader
            ref="uploader"
            :container="container.id"
            :path="path"
            :enabled="!preventDragging && canUpload"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
            v-slot="{ dragging }"
        >
            <div>
                <div class="drag-notification" v-show="dragging">
                    <Icon name="upload" class="m-4 size-12" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <Listing
                    ref="listing"
                    :url="requestUrl"
                    :columns="columns"
                    :action-url="actionUrl"
                    :action-context="actionContext"
                    :allow-bulk-actions="allowBulkActions"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    :preferences-prefix="preferencesPrefix"
                    v-model:search-query="searchQuery"
                    @request-completed="listingRequestCompleted"
                >
                    <template #initializing>
                        <slot name="initializing">
                            <Icon name="loading" />
                        </slot>
                    </template>
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

                            <div class="flex items-center gap-3 py-3 relative">
                                <div class="flex flex-1 items-center gap-3">
                                    <ListingSearch />
                                </div>
                                <ListingCustomizeColumns />
                            </div>
                        </slot>

                        <div
                            v-if="containerIsEmpty"
                            class="rounded-lg border border-dashed border-gray-300 p-6 text-center text-gray-500"
                            v-text="__('No results')"
                        />

                        <Panel v-else :class="{ 'relative overflow-x-auto overscroll-x-contain': mode === 'table' }">
                            <PanelHeader class="flex items-center justify-between p-1!">
                                <Breadcrumbs
                                    v-if="!restrictFolderNavigation"
                                    :path="path"
                                    @navigated="selectFolder"
                                />

                                <Slider
                                    v-if="mode === 'grid'"
                                    size="sm"
                                    class="mr-2 w-24!"
                                    variant="subtle"
                                    v-model="gridThumbnailSize"
                                    :min="60"
                                    :max="300"
                                    :step="25"
                                />
                            </PanelHeader>

                            <Uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                :allow-selecting-existing="allowSelectingExistingUpload"
                                class="mb-3 rounded-lg"
                                @existing-selected="existingUploadSelected"
                            />

                            <Table
                                v-if="mode === 'table'"
                                :assets="items"
                                :folders="folders"
                                :columns="columns"
                                :visible-columns="visibleColumns"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                            />

                            <Grid
                                v-if="mode === 'grid'"
                                :assets="items"
                                :action-url="actionUrl"
                                :thumbnail-size="gridThumbnailSize"
                                :selected-assets="selectedAssets"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                            />

                            <PanelFooter>
                                <ListingPagination />
                            </PanelFooter>
                        </Panel>
                    </template>
                </Listing>
            </div>
        </Uploader>

        <AssetEditor
            v-if="showAssetEditor"
            :id="editedAssetId"
            :read-only="!canEdit"
            @previous="editPreviousAsset"
            @next="editNextAsset"
            @closed="closeAssetEditor"
            @saved="assetSaved"
            @action-started="actionStarted"
            @action-completed="actionCompleted"
        />
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
    ListingSearch,
    ListingCustomizeColumns,
    Slider,
    Icon,
    ToggleGroup,
    ToggleItem,
} from '@statamic/ui';
import { Listing, ListingTable, ListingPagination } from '@statamic/ui';
import Breadcrumbs from './Breadcrumbs.vue';

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
        ListingCustomizeColumns,
        Breadcrumbs,
        Slider,
        Icon,
        ToggleGroup,
        ToggleItem,
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
            return this.searchQuery
                ? cp_url(
                      `assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ''}`,
                  ).replace(/\/$/, '')
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`).replace(/\/$/, '');
        },

        actionContext() {
            return { container: this.container.id };
        },

        canCreateFolders() {
            return (
                this.folder &&
                this.container.create_folders &&
                !this.restrictFolderNavigation &&
                (this.can('upload ' + this.container.id + ' assets') || this.can('configure asset containers'))
            );
        },

        canEdit() {
            return this.can('edit ' + this.container.id + ' assets') || this.can('configure asset containers');
        },

        canUpload() {
            return (
                this.folder &&
                this.container.allow_uploads &&
                (this.can('upload ' + this.container.id + ' assets') || this.can('configure asset containers'))
            );
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
                canEdit: this.canEdit,
                containerIsEmpty: this.containerIsEmpty,
                folder: this.folder,
                folderActionUrl: this.folderActionUrl,
                folders: this.folders,
                restrictFolderNavigation: this.restrictFolderNavigation,
                path: this.path,
                creatingFolder: this.creatingFolder,
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
                'cancel-creating-folder': () => (this.creatingFolder = false),
                'prevent-dragging': (preventDragging) => (this.preventDragging = preventDragging),
            };
        },
    },

    mounted() {
        this.mode = this.getPreference('mode') || 'table';
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

        path() {
            this.loadAssets();
        },

        searchQuery() {
            this.page = 1;
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
        modeChanged(mode) {
            this.mode = mode;
        },

        startCreatingFolder() {
            this.creatingFolder = true;
        },

        listingRequestCompleted({ response }) {
            this.assets = response.data.data;

            if (this.searchQuery) {
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

        actionCompleted() {
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

                    this.$refs.grid?.clearNewFolderName();
                    this.$refs.table?.clearNewFolderName();
                })
                .catch((e) => {
                    if (e.response && e.response.status === 422) {
                        const { message, errors } = e.response.data;

                        errors.directory
                            ? this.$toast.error(errors.directory[0])
                            : this.$toast.error(message);

                        this.$refs.grid?.focusNewFolderInput();
                        this.$refs.table?.focusNewFolderInput();
                    } else {
                        this.$toast.error(__('Something went wrong'));
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
            this.$refs.uploader.browse();
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

        uploadsUpdated(uploads) {
            this.uploads = uploads;
        },
    },
};
</script>
