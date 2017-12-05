export default {

    props: ['folder'],


    methods: {

        selectFolder() {
            this.$emit('selected', this.folder.path);
        },

        editFolder() {
            this.$emit('editing', this.folder.path);

            this.showActionsDropdown = false;
        },

        deleteFolder() {
            this.$emit('deleting', this.folder.path);

            this.showActionsDropdown = false;
        },

        drop(e) {
            this.$emit('dropped-on-folder', this.folder.path, e);
        }

    }

}
