<template>
    <div>
        <div class="flex justify-between items-center mb-6">
            <h1>{{ __('Blueprints') }}</h1>

            <div>
                <button
                    v-if="initialRows.length > 1"
                    class="btn"
                    :class="{ 'disabled': !hasBeenReordered }"
                    :disabled="!hasBeenReordered"
                    @click="saveOrder"
                >
                    {{ __('Save Order') }}
                </button>

                <a :href="createUrl" class="btn-primary rtl:mr-2 ltr:ml-2">
                    {{ __('Create Blueprint') }}
                </a>
            </div>
        </div>

        <blueprint-listing
            :initial-rows="rows"
            :reorderable="initialRows.length > 1"
            @reordered="reordered"
        ></blueprint-listing>
    </div>
</template>

<script>
import BlueprintListing from '../blueprints/Listing.vue';

export default {
    components: {
        BlueprintListing
    },

    props: {
        initialRows: Array,
        reorderUrl: String,
        createUrl: String,
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
                .then(response => {
                    this.$toast.success(__('Blueprints successfully reordered'));

                    this.hasBeenReordered = false
                })
                .catch(error => this.$toast.error(__('Something went wrong')))
        }
    }
}
</script>
