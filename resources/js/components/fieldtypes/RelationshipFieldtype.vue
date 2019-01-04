<template>

    <relationship-input
        v-model="selections"
        :initial-data="initialData"
        :max-items="maxItems"
        :item-data-url="itemDataUrl"
        :selections-url="selectionsUrl"
        :status-icons="true"
        :editable-items="true"
        :columns="['title', 'url']"
        :searchable="true"
    />

</template>

<script>
import qs from 'qs';

export default {

    mixins: [Fieldtype],

    data() {
        return {
            selections: _.clone(this.value),
            initialData: this.meta.data
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
            return this.baseSelectionsUrl + '?' + qs.stringify(this.selectionsUrlParameters);
        },

        baseSelectionsUrl() {
            return cp_url(`fieldtypes/relationship`);
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
