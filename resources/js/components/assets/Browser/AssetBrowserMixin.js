export default {
    props: {
        actionUrl: String,
        canEdit: Boolean,
        containerIsEmpty: Boolean,
        folder: Object,
        folderActionUrl: String,
        folders: Array,
        path: String,
        restrictFolderNavigation: Boolean,
        creatingFolder: Boolean,
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
    },

    methods: {
        actionCompleted() {
            this.$emit('action-completed');
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

        handleFolderDrop(destinationFolder) {
            if (this.draggingAsset) {
                let asset = this.assets.find((asset) => asset.id === this.draggingAsset);
                let action = asset.actions.find((action) => action.handle === 'move_asset');

                if (!action) {
                    return;
                }

                const payload = {
                    action: action.handle,
                    context: action.context,
                    selections: [this.draggingAsset],
                    values: { folder: destinationFolder.path },
                };

                this.$axios
                    .post(this.actionUrl, payload)
                    .then(response => this.$emit('action-completed', true, response))
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
                    .then(response => this.$emit('action-completed', true, response))
                    .finally(() => this.draggingFolder = null);
            }
        },
    },
};
