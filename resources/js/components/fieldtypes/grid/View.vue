<script>
export default {

    props: ['fields', 'rows', 'name'],

    data() {
        return {
            sortableRows: this.rows
        }
    },

    computed: {

        sortableHandleClass() {
            return `${this.name}-drag-handle`;
        }

    },

    watch: {

        sortableRows(rows) {
            this.$emit('sorted', rows);
        }

    }

}
</script>
