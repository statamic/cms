<script>
export default {

    props: ['fields', 'rows', 'meta', 'name', 'canDeleteRows', 'canAddRows'],

    inject: ['grid'],

    computed: {

        sortableItemClass() {
            return `${this.name}-sortable-item`;
        },

        sortableHandleClass() {
            return `${this.name}-drag-handle`;
        },

        fieldPathPrefix() {
            return this.grid.fieldPathPrefix || this.grid.handle;
        }

    },

    provide() {
        return {
            sortableItemClass: this.sortableItemClass,
            sortableHandleClass: this.sortableHandleClass
        }
    },

}
</script>
