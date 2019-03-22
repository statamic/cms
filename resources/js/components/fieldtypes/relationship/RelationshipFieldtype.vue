<template>

    <relationship-input
        :name="name"
        v-model="selections"
        :can-edit="canEdit"
        :config="config"
        :can-create="canCreate"
        :can-reorder="canReorder"
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

    inject: {
        storeName: {
            default: null
        }
    },

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
            return this.baseSelectionsUrl + '?' + qs.stringify({
                config: this.configParameter,
                ...this.meta.getBaseSelectionsUrlParameters,
            });
        },

        baseSelectionsUrl() {
            return this.meta.baseSelectionsUrl;
        },

        configParameter() {
            return btoa(JSON.stringify(this.config));
        },

        site() {
            if (! this.storeName) return this.$config.get('selectedSite');

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

        canReorder() {
            return this.config.max_items > 1;
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
