<template>
    <relationship-input
        ref="input"
        :name="name"
        :value="value"
        :mode="config.mode"
        :can-edit="canEdit"
        :config="config"
        :can-create="canCreate"
        :can-reorder="canReorder"
        :site="site"
        :data="meta ? meta.data : []"
        :max-items="maxItems"
        :item-component="itemComponent"
        :item-data-url="itemDataUrl"
        :filters-url="filtersUrl"
        :selections-url="selectionsUrl"
        :creatables="creatables"
        :form-component="formComponent"
        :form-component-props="formComponentProps"
        :form-stack-size="formStackSize"
        :status-icons="statusIcons"
        :columns="columns"
        :search="canSearch"
        :read-only="isReadOnly"
        :taggable="taggable"
        :tree="meta.tree"
        :initial-sort-column="meta.initialSortColumn"
        :initial-sort-direction="meta.initialSortDirection"
        @focus="$emit('focus')"
        @blur="$emit('blur')"
        @input="update"
        @item-data-updated="itemDataUpdated"
    />
</template>

<script>
import Fieldtype from '../Fieldtype.vue';
import qs from 'qs';

export default {
    mixins: [Fieldtype],

    data() {
        return {
            //
        };
    },

    inject: ['store'],

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

        filtersUrl() {
            return this.meta.filtersUrl + '?' + qs.stringify({ config: this.configParameter });
        },

        selectionsUrl() {
            return (
                this.baseSelectionsUrl +
                '?' +
                qs.stringify({
                    config: this.configParameter,
                    ...this.meta.getBaseSelectionsUrlParameters,
                })
            );
        },

        baseSelectionsUrl() {
            return this.meta.baseSelectionsUrl;
        },

        configParameter() {
            return utf8btoa(JSON.stringify(this.config));
        },

        site() {
            if (this.store) {
                return this.store.site || this.$config.get('selectedSite');
            }

            return this.$config.get('selectedSite');
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

        formStackSize() {
            return this.meta.formStackSize;
        },

        taggable() {
            return this.meta.taggable;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.value.map((id) => {
                const item = this.meta.data.find((d) => d.id === id);
                return item ? item.title : id;
            });
        },

        internalFieldActions() {
            return [
                {
                    title: __('Unlink All'),
                    dangerous: true,
                    run: this.unlinkAll,
                    visible: this.value.length > 0,
                },
            ];
        },
    },

    methods: {
        itemDataUpdated(data) {
            const meta = clone(this.meta);
            meta.data = data;
            this.updateMeta(meta);
        },

        linkExistingItem() {
            this.$refs.input.$refs.existing.$el.click();
        },

        unlinkAll() {
            this.update([]);
            this.updateMeta({
                ...this.meta,
                data: [],
            });
        },
    },
};
</script>
