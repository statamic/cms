<template>
    <div>
        <Header :title="__('Blueprints')" icon="blueprints">
            <Button v-if="reorderable" :disabled="!hasBeenReordered" @click="saveOrder">
                {{ __('Save Order') }}
            </Button>

            <Button :text="__('Create Blueprint')" :href="createUrl" variant="primary" />
        </Header>

        <BlueprintListing
            :initial-rows="rows"
            :reorderable="reorderable"
            @reordered="reordered"
        />
    </div>
</template>

<script>
import { Header, Button } from '@/components/ui';
import BlueprintListing from '../blueprints/Listing.vue';

export default {
    components: {
        Header,
        Button,
        BlueprintListing,
    },

    props: {
        initialRows: Array,
        reorderUrl: String,
        createUrl: String,
    },

    data() {
        return {
            rows: this.initialRows,
            hasBeenReordered: false,
        };
    },

    computed: {
        reorderable() {
            return this.rows.length > 1;
        },
    },

    methods: {
        reordered(rows) {
            this.rows = rows;
            this.hasBeenReordered = true;
        },

        saveOrder() {
            let order = this.rows.map((blueprint) => blueprint.handle);

            this.$axios
                .post(this.reorderUrl, { order })
                .then((response) => this.$toast.success(__('Blueprints successfully reordered')))
                .catch((error) => this.$toast.error(__('Something went wrong')));
        },
    },
};
</script>
