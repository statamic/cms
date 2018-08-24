<script>
export default {
    props: {
        columns: {
            required: true,
        },
        visibleColumns: {
            required: true,
        },
        rows: {
            required: true,
        },
    },
    provide() {
        return {
            sharedState: this.sharedState
        }
    },
    data() {
        return {
            sharedState: {
                checkedIds: [],
                searchQuery: this.searchQuery,
                columns: this.columns,
                visibleColumns: this.visibleColumns,
                rows: this.rows
            }
        }
    },
    render() {
        return this.$scopedSlots.default( {} );
    }
}
</script>
