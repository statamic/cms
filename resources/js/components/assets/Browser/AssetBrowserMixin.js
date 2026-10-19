export default {
    props: {
        actionUrl: String,
        containerIsEmpty: Boolean,
        folder: Object,
        folderActionUrl: String,
        folders: Array,
        path: String,
        restrictFolderNavigation: Boolean,
        creatingFolder: Boolean,
        creatingFolderError: Boolean,
        selectedAssets: { type: Array, default: () => [] },
    },

    data() {
        return {
            newFolderName: null,
            draggingAsset: null,
            draggingFolder: null,
            moving: [],
        }
    },

    computed: {
        draggedAssetIds() {
            if (!this.draggingAsset) return [];

            // Dragging a selected asset brings the rest of the selection along with it.
            if (!this.selectedAssets.includes(this.draggingAsset)) return [this.draggingAsset];

            return this.assets
                .filter((asset) => asset.id === this.draggingAsset || (this.selectedAssets.includes(asset.id) && this.canMoveAsset(asset)))
                .map((asset) => asset.id);
        },
    },

    watch: {
        assets() {
            // Moved items vanish once the listing refreshes, so stop tracking them.
            this.moving = [];
        },

        draggingAsset() {
            this.$emit('prevent-dragging', this.draggingAsset !== null);
        },

        draggingFolder() {
            this.$emit('prevent-dragging', this.draggingFolder !== null);
        },

        newFolderName() {
            if (this.creatingFolderError) this.$emit('update:creatingFolderError', null);
        }
    },

    methods: {
        actionCompleted(successful = null, response = {}) {
            successful ? this.actionSuccess(response) : this.actionFailed(response);

            this.$emit('action-completed');
        },

        actionSuccess(response) {
            if (response.message !== false) {
                Statamic.$toast.success(response.message || __('Action completed'));
            }
        },

        actionFailed(response) {
            Statamic.$toast.error(response.message || __('Action failed'));
        },

        actionStarted() {
            this.$emit('action-started');
        },

        edit(id) {
            this.$emit('edit', id);
        },

        folderActions(folder) {
            return folder.actions || this.folder.actions || [];
        },

        selectFolder(path) {
            this.$emit('select-folder', path);
        },

        focusNewFolderInput() {
            this.$refs.newFolderInput?.edit();
        },

        clearNewFolderName() {
            this.newFolderName = null;
        },

        canMoveAsset(asset) {
            return asset.actions.some((action) => action.handle === 'move_asset');
        },

        canMoveFolder(folder) {
            return folder.actions.some((action) => action.handle === 'move_asset_folder');
        },

        handleFolderDrop(destinationFolder) {
            if (this.draggingAsset) {
                let asset = this.assets.find((asset) => asset.id === this.draggingAsset);
                let action = asset.actions.find((action) => action.handle === 'move_asset');

                if (action) this.moveItems(this.actionUrl, action, this.draggedAssetIds, destinationFolder.path);

                this.draggingAsset = null;
            }

            if (this.draggingFolder) {
                let folder = this.folders.find((folder) => folder.path === this.draggingFolder);
                let action = folder.actions.find((action) => action.handle === 'move_asset_folder');

                if (action) this.moveItems(this.folderActionUrl, action, [folder.path], destinationFolder.path);

                this.draggingFolder = null;
            }
        },

        moveItems(url, action, items, destination) {
            const payload = {
                action: action.handle,
                context: action.context,
                selections: items,
                values: { folder: destination },
            };

            this.moving.push(...items);
            this.actionStarted();

            this.$axios
                .post(url, payload)
                .then(() => {
                    const folder = destination === '/' ? __('All') : destination.split('/').pop();

                    Statamic.$toast.success(__('Moved to :folder', { folder }));
                    Statamic.$events.$emit('removeFromSelections', items);
                    this.$emit('action-completed');
                })
                .catch((error) => {
                    this.moving = this.moving.filter((moving) => !items.includes(moving));
                    this.actionCompleted(false, error.response?.data);
                });
        },

        isDragging(id) {
            return this.draggedAssetIds.includes(id);
        },

        isMoving(item) {
            return this.moving.includes(item);
        },
    },
};
