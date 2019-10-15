<script>
export default {

    props: ['fields', 'rows', 'meta', 'name'],

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

}
</script>
