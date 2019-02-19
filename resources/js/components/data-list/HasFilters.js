export default {

    props: {
        filters: Array,
    },

    data() {
        return {
            activeFilters: {},
        }
    },

    methods: {

        filtersChanged(filters) {
            this.activeFilters = filters;
            this.unselectAllItems();
        },

        unselectAllItems() {
            this.$refs.toggleAll.uncheckAllItems();
        }

    }

}
