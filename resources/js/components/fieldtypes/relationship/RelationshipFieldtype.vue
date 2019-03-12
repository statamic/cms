<template>

    <relationship-input
        :name="name"
        v-model="selections"
        :can-edit="canEdit"
        :can-create="canCreate"
        :site="site"
        :initial-data="initialData"
        :max-items="maxItems"
        :item-component="itemComponent"
        :item-data-url="itemDataUrl"
        :selections-url="selectionsUrl"
        :status-icons="statusIcons"
        :columns="columns"
        :search="canSearch"
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

    inject: ['storeName'],

    computed: {

        maxItems() {
            return this.config.max_items || Infinity;
        },

        columns() {
            return this.meta.columns;
        },

        itemComponent() {
            return this.meta.itemComponent;
        },

        itemDataUrl() {
            return this.meta.itemDataUrl + '?' + qs.stringify({ config: this.configParameter });
        },

        selectionsUrl() {
            return this.baseSelectionsUrl + '?' + qs.stringify(this.selectionsUrlParameters);
        },

        baseSelectionsUrl() {
            return this.meta.baseSelectionsUrl;
        },

        configParameter() {
            return btoa(JSON.stringify(this.config));
        },

        selectionsUrlParameters() {
            let params = { config: this.configParameter };

            if (this.config.collections) {
                params.collections = this.config.collections;
            }

            return params;
        },

        site() {
            return this.$store.state.publish[this.storeName].site;
        },

        canEdit() {
            return this.meta.canEdit;
        },

        canCreate() {
            return this.meta.canCreate;
        },

        canSearch() {
            return this.meta.canSearch;
        },

        statusIcons() {
            return this.meta.statusIcons;
        }

    },

    watch: {

        selections(selections) {
            this.update(this.selections);
        }

    }

}
</script>
