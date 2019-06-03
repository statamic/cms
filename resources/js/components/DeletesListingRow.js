export default {
    data() {
        return {
            deletingRow: false
        }
    },

    computed: {
        deletingModalTitle() {
            return this.deletingModalTitleFromRowKey('title');
        }
    },

    methods: {
        confirmDeleteRow(id, index) {
            this.deletingRow = {id, index}
        },

        deletingModalTitleFromRowKey(key) {
            return __('Delete') + ' ' + this.rows[this.deletingRow.index][key];
        },

        deleteRow(resourceRoute) {
            this.$axios.delete(`${resourceRoute}/${this.deletingRow.id}`)
                .then(() => {
                    this.removeRowOrReload();
                })
                .catch(() => {
                    this.$notify.error(__('Something went wrong'));
                });
        },

        removeRowOrReload(index) {
            this.rows.splice(this.deletingRow.index, 1);

            // If there are no rows left, reload browser to show fresh 'create first' state.
            if (this.rows.length === 0) {
                location.reload();
                return;
            }

            this.cancelDeleteRow();
        },

        cancelDeleteRow() {
            this.deletingRow = false;
        }
    }
}
