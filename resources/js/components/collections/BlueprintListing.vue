<script>
import BlueprintListing from '../blueprints/Listing.vue';

export default {

    components: {
        BlueprintListing
    },

    props: {
        initialRows: Array,
        reorderUrl: String
    },

    data() {
        return {
            rows: this.initialRows,
            hasBeenReordered: false
        }
    },

    methods: {
        reordered(rows) {
            this.rows = rows;
            this.hasBeenReordered = true;
        },

        saveOrder() {
            let order = this.rows.map(blueprint => blueprint.handle);

            this.$axios
                .post(this.reorderUrl, { order })
                .then(response => this.$toast.success(__('Blueprints successfully reordered')))
                .catch(error => this.$toast.error(__('Something went wrong')))
        }
    }

}
</script>
