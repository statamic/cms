export default {

    props: {
        filters: Array,
    },

    data() {
        return {
            activeFilters: {},
        }
    },

    created() {
        this.$events.$on('filters-changed', this.filtersChanged);
    },

    methods: {

        filtersChanged(filters) {
            this.activeFilters = filters;
            this.unselectAllItems();
        },

        unselectAllItems() {
            if (this.$refs.toggleAll) {
                this.$refs.toggleAll.uncheckAllItems();
            }
        }

    }

}
