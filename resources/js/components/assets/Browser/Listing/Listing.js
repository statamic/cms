export default {

    props: [
        'container',
        'assets',
        'folder',
        'subfolders',
        'loading',
        'selectedAssets',
        'restrictNavigation',
        'isSearching'
    ],


    computed: {

        hasResults() {
            return this.assets.length || this.subfolders.length;
        },

        hasParent() {
            if (! this.folder) {
                return false;
            }

            return this.folder.parent_path !== null;
        }

    },


    methods: {

        /**
         * Select a folder to navigate to.
         */
        selectFolder(path) {
            this.$emit('folder-selected', path);
        },

        /**
         * Select (check) an asset.
         */
        selectAsset(id) {
            if (this.can('assets:'+ this.container +':edit')) {
                this.$emit('asset-selected', id);
            }
        },

        /**
         * Deselect (uncheck) an asset.
         */
        deselectAsset(id) {
            this.$emit('asset-deselected', id);
        },

        /**
         * Trigger editing of this asset.
         */
        editAsset(id) {
            this.$emit('asset-editing', id);
        },

        /**
         * Trigger the deleting of this asset.
         */
        deleteAsset(id) {
            this.$emit('asset-deselected', id);
            this.$emit('asset-deleting', id);
        },

        assetDoubleclicked(id) {
            this.$emit('asset-doubleclicked');
        },

        /**
         * Trigger editing of this folder.
         */
        editFolder(path) {
            this.$emit('folder-editing', path);
        },

        /**
         * Delete a folder.
         */
        deleteFolder(path) {
            const url = cp_url('assets/folders/delete');

            swal({
                type: 'warning',
                title: __('Are you sure?'),
                text: __('This folder will be deleted'),
                confirmButtonText: __('Yes, I\'m sure'),
                cancelButtonText: __('Cancel'),
                showCancelButton: true
            }, () => {
                this.$http.delete(url, {
                    container: this.container,
                    folders: path
                }).success((response) => {
                    this.$emit('folder-deleted', path);
                    this.saving = false;
                });
            });
        },

        assetDragStart(id) {
            this.selectAsset(id);
            this.draggingAssets = true;
        }

    }

}
