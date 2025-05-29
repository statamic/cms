<template>
    <div class="min-h-screen" ref="browser" @keydown.shift="shiftDown" @keyup="clearShift">
        <Header :title="__(container.title)" icon="assets">
            <Dropdown v-if="container.can_edit || container.can_delete || container.can_create">
                <DropdownMenu>
                    <DropdownItem
                        v-if="canCreateContainers"
                        :text="__('Create Container')"
                        :href="createContainerUrl"
                    />
                    <DropdownItem
                        v-if="container.can_edit"
                        :text="__('Edit Container')"
                        :href="container.edit_url"
                    />
                    <DropdownItem
                        :text="__('Edit Blueprint')"
                        :href="container.blueprint_url"
                    />
                    <DropdownItem
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
            <Button v-if="canCreateFolders" :text="__('Create Folder')" icon="folder-add" @click="creatingFolder = true" />

            <ui-toggle-group v-model="mode">
                <ui-toggle-item icon="layout-grid" value="grid" />
                <ui-toggle-item icon="layout-list" value="table" />
            </ui-toggle-group>
        </Header>

        <div v-if="initializing" class="loading">
            <loading-graphic />
        </div>

        <uploader
            v-if="!initializing"
            ref="uploader"
            :container="container.id"
            :path="path"
            :enabled="!preventDragging && canUpload"
            @updated="uploadsUpdated"
            @upload-complete="uploadCompleted"
            @error="uploadError"
            v-slot="{ dragging }"
        >
            <div class="min-h-screen">
                <div class="drag-notification" v-show="dragging">
                    <svg-icon name="upload" class="m-4 size-12" />
                    <span>{{ __('Drop File to Upload') }}</span>
                </div>

                <data-list
                    v-if="!initializing"
                    :rows="assets"
                    :columns="columns"
                    :selections="selectedAssets"
                    :max-selections="maxFiles"
                    :sort-column="sortColumn"
                    :sort-direction="sortDirection"
                    @selections-updated="(ids) => $emit('selections-updated', ids)"
                    v-slot="{ filteredRows: rows }"
                >
                    <div :class="modeClass">
                        <div class="space-y-4">
                            <data-list-search ref="search" v-model="searchQuery" />

                            <breadcrumbs v-if="!restrictFolderNavigation" :path="path" @navigated="selectFolder" />

                            <uploads
                                v-if="uploads.length"
                                :uploads="uploads"
                                :allow-selecting-existing="allowSelectingExistingUpload"
                                :class="{ '-mt-px': !hasSelections, 'mt-10': hasSelections }"
                                @existing-selected="existingUploadSelected"
                            />

                            <Table
                                ref="table"
                                v-if="mode === 'table'"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                                :columns="columns"
                                :loading="loading"
                                @sorted="sorted"
                            />

                            <!-- Grid Mode -->
                            <Grid
                                ref="grid"
                                v-if="mode === 'grid'"
                                v-bind="sharedAssetProps"
                                v-on="sharedAssetEvents"
                                :assets="assets"
                                :selected-assets="selectedAssets"
                                @toggle-selection="toggleSelection"
                            >
                                <template #footer>
                                    <data-list-pagination
                                        :resource-meta="meta"
                                        :per-page="perPage"
                                        @page-selected="page = $event"
                                        @per-page-changed="changePerPage"
                                    />
                                </template>
                            </Grid>

                            <div
                                class="p-4 text-gray-700"
                                v-if="containerIsEmpty"
                                v-text="searchQuery ? __('No results') : __('This container is empty')"
                            />
                        </div>

                        <!-- <data-list-pagination
                            class="mt-6"
                            :resource-meta="meta"
                            :per-page="perPage"
                            @page-selected="page = $event"
                            @per-page-changed="changePerPage"
                        /> -->

                        <BulkActions
                            :url="actionUrl"
                            :selections="selections"
                            :context="actionContext"
                            @started="actionStarted"
                            @completed="actionCompleted"
                            v-slot="{ actions }"
                        >
                            <div class="fixed inset-x-0 bottom-1 z-100 flex w-full justify-center">
                                <ButtonGroup>
                                    <Button
                                        variant="primary"
                                        class="text-gray-400!"
                                        :text="__n(`:count item selected|:count items selected`, selections.length)"
                                    />
                                    <Button
                                        v-for="action in actions"
                                        :key="action.handle"
                                        variant="primary"
                                        :text="__(action.title)"
                                        @click="action.run"
                                    />
                                </ButtonGroup>
                            </div>
                        </BulkActions>
                    </div>
                </data-list>
            </div>
        </uploader>

        <asset-editor
            v-if="showAssetEditor"
            :id="editedAssetId"
            :read-only="!canEdit"
            @previous="editPreviousAsset"
            @next="editNextAsset"
            @closed="closeAssetEditor"
            @saved="assetSaved"
        />
    </div>
</template>

<script>
import AssetThumbnail from './Thumbnail.vue';
import AssetEditor from '../Editor/Editor.vue';
import Grid from './Grid.vue';
import Table from './Table.vue';
import HasPagination from '../../data-list/HasPagination';
import HasPreferences from '../../data-list/HasPreferences';
import Uploader from '../Uploader.vue';
import Uploads from '../Uploads.vue';
import HasActions from '../../data-list/HasActions';
import { keyBy, sortBy } from 'lodash-es';
import { Header, Button, ButtonGroup, Dropdown, DropdownItem, DropdownMenu } from '@statamic/ui';
import BulkActions from '@statamic/components/data-list/BulkActions.vue';

export default {
    mixins: [HasActions, HasPagination, HasPreferences],

    components: {
        DropdownMenu,
        DropdownItem,
        Dropdown,
        AssetThumbnail,
        AssetEditor,
        Uploader,
        Uploads,
        Grid,
        Table,
        Header,
        Button,
        ButtonGroup,
        BulkActions,
    },

    props: {
        allowSelectingExistingUpload: Boolean,
        autofocusSearch: Boolean,
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
    },

    data() {
        return {
            columns: [
                { label: __('File'), field: 'basename', visible: true, sortable: true },
                { label: __('Size'), field: 'size', value: 'size_formatted', visible: true, sortable: true },
                {
                    label: __('Last Modified'),
                    field: 'last_modified',
                    value: 'last_modified_relative',
                    visible: true,
                    sortable: true,
                },
            ],
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
            preferencesPrefix: null,
            meta: {},
            sortColumn: this.container.sort_field,
            sortDirection: this.container.sort_direction,
            mode: 'table',
            actionUrl: null,
            folderActionUrl: null,
            shifting: false,
            lastItemClicked: null,
            preventDragging: false,
        };
    },

    computed: {
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

        modeClass() {
            return 'mode-' + this.mode;
        },

        parameters() {
            return {
                page: this.page,
                perPage: this.perPage,
                sort: this.sortColumn,
                order: this.sortDirection,
                search: this.searchQuery,
                queryScopes: this.queryScopes,
            };
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

    created() {
        this.$events.$on('editor-action-started', this.actionStarted);
        this.$events.$on('editor-action-completed', this.actionCompleted);
    },

    mounted() {
        this.initializing = true;
        this.preferencesPrefix = `assets.${this.container.id}`;
        this.mode = this.getPreference('mode') || 'table';
        this.setInitialPerPage();
        this.loadAssets();
    },

    unmounted() {
        this.$events.$off('editor-action-started', this.actionStarted);
        this.$events.$off('editor-action-completed', this.actionCompleted);
    },

    watch: {
        mode(mode) {
            this.setPreference('mode', mode == 'table' ? null : mode);
        },

        editedAssetId(editedAssetId) {
            let path = editedAssetId
                ? [this.path, this.editedAssetBasename].filter((value) => value != '/').join('/') + '/edit'
                : this.path;

            this.$emit('navigated', path);
        },

        initializing(isInitializing, wasInitializing) {
            if (wasInitializing && this.autofocusSearch) {
                this.$nextTick(() => this.$refs.search.focus());
            }
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
    },

    methods: {
        afterActionSuccessfullyCompleted() {
            this.loadAssets();
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

        isSelected(id) {
            return this.selectedAssets.includes(id);
        },

        loadAssets() {
            this.loading = true;

            const url = this.searchQuery
                ? cp_url(
                      `assets/browse/search/${this.container.id}/${this.restrictFolderNavigation ? this.path : ''}`,
                  ).replace(/\/$/, '')
                : cp_url(`assets/browse/folders/${this.container.id}/${this.path || ''}`).replace(/\/$/, '');

            return this.$axios
                .get(url, { params: this.parameters })
                .then((response) => {
                    const data = response.data;
                    this.assets = data.data.assets;
                    this.meta = data.meta;

                    if (this.searchQuery) {
                        this.folder = null;
                        this.folders = [];
                    } else {
                        this.folder = data.data.folder;
                        this.folders = data.data.folder.folders;
                        this.actionUrl = data.links.asset_action;
                        this.folderActionUrl = data.links.folder_action;
                    }

                    this.loading = false;
                    this.initializing = false;
                })
                .catch((e) => {
                    this.$toast.error(e.response.data.message, { action: null, duration: null });
                    this.assets = [];
                    this.folders = [];
                    this.loading = false;
                    this.initializing = false;
                });
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

        setMode(mode) {
            this.mode = mode;
            this.setPreference('mode', mode == 'table' ? null : mode);
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
