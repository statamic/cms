<script>
export default {

    props: ['fields', 'rows', 'meta', 'name'],

    data() {
        return {
            sortableRows: this.rows
        }
    },

    computed: {

        sortableItemClass() {
            return `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return `${this.name}-drag-handle`;
        }

    },

    provide() {
        return {
            sortableItemClass: this.sortableItemClass,
            sortableHandleClass: this.sortableHandleClass
        }
    },

    watch: {
        rows(rows) {
            if (JSON.stringify(this.sortableRows) == JSON.stringify(rows)) {
                return;
            }
            this.sortableRows = rows;
        },

        sortableRows(rows) {
            this.$emit('sorted', rows);
        }

    }

}
</script>
