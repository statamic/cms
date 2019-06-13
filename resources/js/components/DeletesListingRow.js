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
                    location.reload();
                })
                .catch(() => {
                    this.$notify.error(__('Something went wrong'));
                });
        },

        cancelDeleteRow() {
            this.deletingRow = false;
        }
    }
}
