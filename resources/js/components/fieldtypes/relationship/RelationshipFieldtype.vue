<template>

    <relationship-input
        :name="name"
        v-model="selections"
        :mode="config.mode"
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
        :creatables="creatables"
        :form-component="formComponent"
        :form-component-props="formComponentProps"
        :status-icons="statusIcons"
        :columns="columns"
        :search="canSearch"
        :read-only="isReadOnly"
        :taggable="taggable"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        @item-data-updated="itemDataUpdated"
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
            return this.maxItems > 1;
        },

        statusIcons() {
            return this.meta.statusIcons;
        },

        creatables() {
            return this.meta.creatables;
        },

        formComponent() {
            return this.meta.formComponent;
        },

        formComponentProps() {
            return this.meta.formComponentProps;
        },

        taggable() {
            return this.meta.taggable;
        },

        replicatorPreview() {
            return this.value.map(id => {
                const item = _.findWhere(this.meta.data, { id });
                return item ? item.title : id;
            });
        }

    },

    watch: {

        selections(selections) {
            this.update(this.selections);
        },

        value(value) {
            if (JSON.stringify(value) == JSON.stringify(this.selections)) return;
            this.selections = value;
        }

    },

    methods: {

        itemDataUpdated(data) {
            const meta = clone(this.meta);
            meta.data = data;
            this.updateMeta(meta);
        }

    }

}
</script>
