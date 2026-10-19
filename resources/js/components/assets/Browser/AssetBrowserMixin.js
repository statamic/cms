import { showMultipleAssetDragBadge } from './dragBadge.js';

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
        moving: { type: Array, default: () => [] },
    },

    data() {
        return {
            newFolderName: null,
            draggingAsset: null,
            draggingFolder: null,
            dragOverFolder: null,
        }
    },

    computed: {
        draggedAssetIds() {
            if (!this.draggingAsset) return [];

            // Dragging a selected asset brings the rest of the selection along with it,
            // except anything still moving from an earlier drop, since its ID is about to change.
            if (!this.selectedAssets.includes(this.draggingAsset)) return [this.draggingAsset];

            return this.assets
                .filter((asset) => asset.id === this.draggingAsset || (this.selectedAssets.includes(asset.id) && this.canMoveAsset(asset) && !this.isMoving(asset.id)))
                .map((asset) => asset.id);
        },

        draggingMovable() {
            if (this.draggingAsset) {
                const asset = this.assets.find((asset) => asset.id === this.draggingAsset);

                return !!asset && this.canMoveAsset(asset);
            }

            if (this.draggingFolder) {
                const folder = this.folders.find((folder) => folder.path === this.draggingFolder);

                return !!folder && this.canMoveFolder(folder);
            }

            return false;
        },
    },

    watch: {
        draggingAsset() {
            this.$emit('prevent-dragging', this.draggingAsset !== null);
        },

        draggingFolder() {
            this.$emit('prevent-dragging', this.draggingFolder !== null);
        },

        draggingMovable(movable) {
            this.$emit('dragging-movable', movable);
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

        assetDragStarted(event, asset) {
            this.draggingAsset = asset.id;

            if (this.draggedAssetIds.length > 1) {
                showMultipleAssetDragBadge(event, this.draggedAssetIds.length);
            }
        },

        canDropOn(folder) {
            return this.draggingMovable && folder.path !== this.draggingFolder;
        },

        folderDragOver(event, folder) {
            if (!this.canDropOn(folder)) return;

            event.preventDefault();
            this.dragOverFolder = folder.path;
        },

        folderDragLeave(event) {
            if (event.currentTarget.contains(event.relatedTarget)) return;

            this.dragOverFolder = null;
        },

        handleFolderDrop(destinationFolder) {
            const destination = destinationFolder.path;

            if (this.draggingAsset && this.draggingMovable) {
                let asset = this.assets.find((asset) => asset.id === this.draggingAsset);
                let action = asset.actions.find((action) => action.handle === 'move_asset');

                // Search results can come from anywhere, so skip assets that are already in the destination.
                let ids = this.assets
                    .filter((asset) => this.draggedAssetIds.includes(asset.id) && parentPath(asset.path) !== destination)
                    .map((asset) => asset.id);

                if (ids.length) this.$emit('move-items', { url: this.actionUrl, action, items: ids, destination });
            }

            if (this.draggingFolder && this.draggingMovable) {
                let folder = this.folders.find((folder) => folder.path === this.draggingFolder);
                let action = folder.actions.find((action) => action.handle === 'move_asset_folder');

                if (folder.path !== destination && parentPath(folder.path) !== destination) {
                    this.$emit('move-items', { url: this.folderActionUrl, action, items: [folder.path], destination });
                }
            }

            this.draggingAsset = null;
            this.draggingFolder = null;
            this.dragOverFolder = null;
        },

        isDragging(id) {
            return this.draggedAssetIds.includes(id);
        },

        isMoving(item) {
            return this.moving.includes(item);
        },
    },
};

function parentPath(path) {
    return path.includes('/') ? path.slice(0, path.lastIndexOf('/')) : '/';
}
