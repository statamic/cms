<template>

    <relationship-input
        v-model="selections"
        :max-items="maxItems"
        :item-data-url="itemDataUrl"
        :selections-url="selectionsUrl"
        :status-icons="true"
        :editable-items="true"
        :columns="['title', 'url']"
    />

</template>

<script>
import qs from 'qs';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            selections: this.value,
        }
    },

    computed: {

        maxItems() {
            return this.config.max_items || Infinity;
        },

        itemDataUrl() {
            return cp_url(`fieldtypes/relationship/data`);
        },

        selectionsUrl() {
            return cp_url(`fieldtypes/relationship`) + '?' + qs.stringify(this.selectionsUrlParameters);
        },

        selectionsUrlParameters() {
            let params = {};

            if (this.config.collections) {
                params.collections = this.config.collections;
            }

            return params;
        }

    },

    watch: {

        selections(selections) {
            this.update(this.selections);
        }

    }

}
</script>
