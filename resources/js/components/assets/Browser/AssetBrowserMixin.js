export default {
    props: {
        containerIsEmpty: Boolean,
        folder: Object,
        folders: Array,
        restrictFolderNavigation: Boolean,
        folderActionUrl: String,
        actionUrl: String,
        canEdit: Boolean,
    },

    methods: {
        selectFolder(path) {
            this.$emit('select-folder', path);
        },

        folderActions(folder) {
            return folder.actions || this.folder.actions || [];
        },

        edit(id) {
            this.$emit('edit', id);
        },

        actionStarted() {
            this.$emit('action-started');
        },

        actionCompleted() {
            this.$emit('action-completed');
        },
    },
};
