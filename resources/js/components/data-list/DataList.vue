<script>
import Fuse from 'fuse.js';

export default {
    props: {
        columns: {
            required: true,
        },
        visibleColumns: {
            required: true,
        },
        rows: {
            type: Array,
            required: true,
        },
        searchQuery: {
            type: String,
            default: ''
        }
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
        var fuse = new Fuse(this.rows, {
            findAllMatches: true,
            threshold: 0.1,
            minMatchCharLength: 2,
            keys: this.visibleColumns
        });

        return this.$scopedSlots.default({
            filteredRows: this.searchQuery ? fuse.search(this.searchQuery) : this.rows
        });
    },
}
</script>
