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
    },
};
