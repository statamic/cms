<template>

    <div class="relationship-input" :class="{ 'relationship-input-empty': items.length == 0 }">

        <relationship-select-field
            v-if="!initializing && usesSelectField"
            :config="config"
            :items="items"
            :multiple="maxItems > 1"
            :typeahead="mode === 'typeahead'"
            :taggable="taggable"
            :read-only="readOnly"
            :url="selectionsUrl"
            :site="site"
            @input="selectFieldSelected"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <loading-graphic v-if="initializing" :inline="true" />

        <template v-if="!initializing && !usesSelectField">
            <div ref="items" class="relationship-input-items space-y-1 outline-none">
                <component
                    :is="itemComponent"
                    v-for="(item, i) in items"
                    :key="item.id"
                    :item="item"
                    :config="config"
                    :status-icon="statusIcons"
                    :editable="canEdit"
                    :sortable="!readOnly && canReorder"
                    :read-only="readOnly"
                    :form-component="formComponent"
                    :form-component-props="formComponentProps"
                    class="item outline-none"
                    @removed="remove(i)"
                />
            </div>

            <div class="py-1 text-xs text-grey" v-if="maxItemsReached && maxItems != 1">
                <span>{{ __('Maximum items selected:')}}</span>
                <span>{{ maxItems }}/{{ maxItems }}</span>
            </div>
            <div v-if="canSelectOrCreate" class="relationship-input-buttons relative" :class="{ 'mt-2': items.length > 0 }" >
                <div class="flex flex-wrap items-center text-sm -mb-1">
                    <div class="relative mb-1">
                        <create-button
                            v-if="canCreate && creatables.length"
                            :creatables="creatables"
                            :site="site"
                            :component="formComponent"
                            :component-props="formComponentProps"
                            @created="itemCreated"
                        />
                    </div>
                    <button ref="existing" class="text-blue hover:text-grey-80 flex mb-1 outline-none" @click.prevent="isSelecting = true">
                        <svg-icon name="hyperlink" class="mr-sm h-4 w-4 flex items-center"></svg-icon>
                        {{ __('Link Existing Item') }}
                    </button>
                </div>
            </div>

            <stack name="item-selector" v-if="isSelecting" @closed="isSelecting = false">
                <item-selector
                    slot-scope="{ close }"
                    :filters-url="filtersUrl"
                    :selections-url="selectionsUrl"
                    :site="site"
                    :initial-columns="columns"
                    initial-sort-column="title"
                    initial-sort-direction="asc"
                    :initial-selections="value"
                    :max-selections="maxItems"
                    :search="search"
                    :exclusions="exclusions"
                    :type="config.type"
                    @selected="selectionsUpdated"
                    @closed="close"
                />
            </stack>

            <input v-if="name" type="hidden" :name="name" :value="JSON.stringify(value)" />
        </template>
    </div>

</template>

<script>
import RelatedItem from './Item.vue';
import ItemSelector from './Selector.vue';
import CreateButton from './CreateButton.vue';
import {Sortable, Plugins} from '@shopify/draggable';
import RelationshipSelectField from './SelectField.vue';

export default {

    props: {
        name: String,
        value: { required: true },
        config: Object,
        data: Array,
        maxItems: Number,
        itemComponent: {
            type: String,
            default: 'RelatedItem',
        },
        itemDataUrl: String,
        filtersUrl: String,
        selectionsUrl: String,
        statusIcons: Boolean,
        site: String,
        search: Boolean,
        canEdit: Boolean,
        canCreate: Boolean,
        canReorder: Boolean,
        readOnly: Boolean,
        exclusions: Array,
        creatables: Array,
        formComponent: String,
        formComponentProps: Object,
        mode: {
            type: String,
            default: 'default',
        },
        taggable: Boolean,
        columns: {
            type: Array,
            default: () => []
        }
    },

    components: {
        ItemSelector,
        RelatedItem,
        CreateButton,
        RelationshipSelectField,
    },

    data() {
        return {
            isSelecting: false,
            isCreating: false,
            itemData: [],
            initializing: true,
            loading: true,
            inline: false,
            sortable: null,
        }
    },

    computed: {

        items() {
            return this.value.map(selection => {
                const data = _.find(this.data, (item) => item.id == selection);

                if (! data) return { id: selection, title: selection };

                return data;
            });
        },

        maxItemsReached() {
            return this.value.length >= this.maxItems;
        },

        canSelectOrCreate() {
            return !this.readOnly && !this.maxItemsReached;
        },

        usesSelectField() {
            return ['select', 'typeahead'].includes(this.mode);
        }

    },

    mounted() {
        this.initializeData().then(() => {
            this.initializing = false;
            if (this.canReorder) {
                this.$nextTick(() => this.makeSortable());
            }
        });
    },

    beforeDestroy() {
        if (this.sortable) {
            this.sortable.destroy();
            this.sortable = null;
        }
        this.setLoadingProgress(false);
    },

    watch: {

        loading: {
            immediate: true,
            handler(loading) {
                this.$emit('loading', loading);
                this.setLoadingProgress(loading);
            }
        },

        isSelecting(selecting) {
            this.$emit(selecting ? 'focus' : 'blur');
        },

        itemData(data, olddata) {
            if (this.initializing) return;
            this.$emit('item-data-updated', data);
        }

    },

    methods: {

        update(selections) {
            if (JSON.stringify(selections) == JSON.stringify(this.value)) return;
            this.$emit('input', selections);
        },

        remove(index) {
            this.update([
                ...this.value.slice(0, index),
                ...this.value.slice(index + 1),
            ]);
        },

        selectionsUpdated(selections) {
            this.getDataForSelections(selections).then(() => {
                this.update(selections);
            });
        },

        initializeData() {
            if (!this.data) {
                return this.getDataForSelections(this.selections);
            }

            this.loading = false;
            return Promise.resolve();
        },

        getDataForSelections(selections) {
            this.loading = true;

            return this.$axios.post(this.itemDataUrl, { site: this.site, selections }).then(response => {
                this.$emit('item-data-updated', response.data.data);
            }).finally(() => {
                this.loading = false;
            });
        },

        makeSortable() {
            this.sortable = new Sortable(this.$refs.items, {
                draggable: '.item',
                handle: '.item-move',
                mirror: { constrainDimensions: true, xAxis: false },
                swapAnimation: { vertical: true },
                plugins: [Plugins.SwapAnimation],
                delay: 200
            }).on('drag:start', e => {
                this.value.length === 1 ? e.cancel() : this.$emit('focus');
            }).on('drag:stop', e => {
                this.$emit('blur');
            }).on('sortable:stop', e => {
                const val = [...this.value];
                val.splice(e.newIndex, 0, val.splice(e.oldIndex, 1)[0]);
                this.update(val);
            })
        },

        itemCreated(item) {
            this.$emit('item-data-updated', [...this.data, item]);
            this.update([...this.value, item.id]);
        },

        selectFieldSelected(selectedItemData) {
            this.$emit('item-data-updated', selectedItemData.map(item => ({ id: item.id, title: item.title })));
            this.update(selectedItemData.map(item => item.id));
        },

        setLoadingProgress(state) {
            this.$progress.loading(`relationship-fieldtype-${this._uid}`, state);
        }

    }

}
</script>
