export default {
    props: {
        actionUrl: String,
        containerIsEmpty: Boolean,
        folder: Object,
        folderActionUrl: String,
        folders: Array,
        path: String,
        selectedAssets: {
            type: Array,
            default: () => [],
        },
        restrictFolderNavigation: Boolean,
        creatingFolder: Boolean,
        creatingFolderError: Boolean,
    },

    data() {
        return {
            newFolderName: null,
            draggingAsset: null,
            draggingFolder: null,
        }
    },

    watch: {
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

        getDraggingAssetSelections() {
            const selectedAssetIds = Array.isArray(this.selectedAssets) ? this.selectedAssets : [];

            if (selectedAssetIds.includes(this.draggingAsset)) {
                return selectedAssetIds;
            }

            return this.draggingAsset ? [this.draggingAsset] : [];
        },

        invokeActionCallback(data) {
            if (!data) {
                return;
            }

            if (Array.isArray(data.callback) && data.callback.length) {
                Statamic.$callbacks.call(data.callback[0], ...data.callback.slice(1));
                return;
            }

            if (
                data.completed_moves &&
                typeof data.completed_moves === 'object' &&
                !Array.isArray(data.completed_moves) &&
                Object.keys(data.completed_moves).length
            ) {
                Statamic.$callbacks.call('replaceInSelections', data.completed_moves);
            }
        },

        handleFolderDrop(destinationFolder) {
            if (this.draggingAsset) {
                let asset = this.assets.find((asset) => asset.id === this.draggingAsset);
                let action = asset.actions.find((action) => action.handle === 'move_asset');
                const selections = this.getDraggingAssetSelections();

                if (!action || selections.length === 0) {
                    return;
                }

                const payload = {
                    action: action.handle,
                    context: action.context,
                    selections,
                    values: { folder: destinationFolder.path },
                };

                this.$axios
                    .post(this.actionUrl, payload)
                    .then(({ data }) => {
                        this.invokeActionCallback(data);

                        if (data.success === false && data.conflict?.type === 'asset_move') {
                            this.$emit('asset-move-conflict', {
                                action,
                                asset,
                                destinationFolder,
                                selections,
                                message: data.message,
                                conflict: data.conflict,
                                completedMoves: data.completed_moves,
                            });

                            return;
                        }

                        this.$emit('action-completed', data.success !== false, data);
                    })
                    .catch((error) => this.$emit('action-completed', false, error.response?.data || {}))
                    .finally(() => this.draggingAsset = null);
            }

            if (this.draggingFolder) {
                let folder = this.folders.find((folder) => folder.path === this.draggingFolder);
                let action = folder.actions.find((action) => action.handle === 'move_asset_folder');

                if (!action) {
                    return;
                }

                const payload = {
                    action: action.handle,
                    context: action.context,
                    selections: [this.draggingFolder],
                    values: { folder: destinationFolder.path },
                };

                this.$axios
                    .post(this.folderActionUrl, payload)
                    .then(({ data }) => {
                        this.invokeActionCallback(data);
                        this.$emit('action-completed', data.success !== false, data);
                    })
                    .catch((error) => this.$emit('action-completed', false, error.response?.data || {}))
                    .finally(() => this.draggingFolder = null);
            }
        },
    },
};
