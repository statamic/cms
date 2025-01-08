<template>

    <div class="relationship-input" :class="{ 'relationship-input-empty': items.length == 0 }">

        <relationship-select-field
            v-if="!initializing && usesSelectField"
            :config="config"
            :multiple="maxItems > 1"
            :typeahead="mode === 'typeahead'"
            :taggable="taggable"
            :read-only="readOnly"
            :url="selectionsUrl"
            :site="site"
            :items="items"
            @update:model-value="selectFieldSelected"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <loading-graphic v-if="initializing" :inline="true" />

        <template v-if="shouldShowSelectedItems">
            <div ref="items" class="relationship-input-items space-y-1 outline-none" :class="{ 'mt-4': usesSelectField && items.length }">
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
                    :form-stack-size="formStackSize"
                    class="item outline-none"
                    @removed="remove(i)"
                />
            </div>

            <div class="py-2 text-xs text-gray" v-if="maxItemsReached && maxItems != 1">
                <span>{{ __('Maximum items selected:')}}</span>
                <span>{{ maxItems }}/{{ maxItems }}</span>
            </div>
            <div v-if="canSelectOrCreate" class="relationship-input-buttons relative @container" :class="{ 'mt-4': items.length > 0 }" >
                <div class="flex flex-wrap items-center text-sm -mb-2">
                    <div class="relative mb-2">
                        <create-button
                            v-if="canCreate && creatables.length"
                            :creatables="creatables"
                            :site="site"
                            :component="formComponent"
                            :component-props="formComponentProps"
                            :stack-size="formStackSize"
                            @created="itemCreated"
                        />
                    </div>
                    <button ref="existing" class="text-blue dark:text-dark-blue-100 hover:text-gray-800 dark:hover:text-dark-100 flex items-center mb-2 outline-none" @click.prevent="isSelecting = true">
                        <svg-icon name="light/hyperlink" class="rtl:ml-1 ltr:mr-1 h-4 w-4 flex items-center"></svg-icon>
                        <span class="hidden @sm:block" v-text="__('Link Existing Item')" />
                        <span class="@sm:hidden" v-text="__('Link')" />
                    </button>
                </div>
            </div>

            <stack name="item-selector" v-if="isSelecting" @closed="isSelecting = false">
                <template #default="{ close }">
                    <item-selector
                        :name="name"
                        :filters-url="filtersUrl"
                        :selections-url="selectionsUrl"
                        :site="site"
                        :initial-columns="columns"
                        :initial-sort-column="initialSortColumn"
                        :initial-sort-direction="initialSortDirection"
                        :initial-selections="modelValue"
                        :max-selections="maxItems"
                        :search="search"
                        :exclusions="exclusions"
                        :type="config.type"
                        :tree="tree"
                        @selected="selectionsUpdated"
                        @closed="close"
                    />
                </template>
            </stack>

            <input v-if="name" type="hidden" :name="name" :value="JSON.stringify(modelValue)" />
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
    emits: ['focus', 'blur', 'loading', 'item-data-updated'],

    props: {
        name: String,
        modelValue: {
            required: true,
            default: () => [],
        },
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
        formStackSize: String,
        mode: {
            type: String,
            default: 'default',
        },
        taggable: Boolean,
        columns: {
            type: Array,
            default: () => []
        },
        tree: Object,
        initialSortColumn: {
            type: String,
            default: 'title'
        },
        initialSortDirection: {
            type: String,
            default: 'asc'
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
            if (this.modelValue === null) return [];

            return this.modelValue?.map(selection => {
                const data = _.find(this.data, (item) => item.id == selection);

                if (! data) return { id: selection, title: selection };

                return data;
            });
        },

        maxItemsReached() {
            return this.modelValue?.length >= this.maxItems;
        },

        canSelectOrCreate() {
            return !this.usesSelectField && !this.readOnly && !this.maxItemsReached;
        },

        usesSelectField() {
            return ['select', 'typeahead'].includes(this.mode);
        },

        shouldShowSelectedItems() {
            if (this.initializing) return false;

            if (this.usesSelectField && this.maxItems === 1) return false;

            return true;
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
            if (JSON.stringify(selections) == JSON.stringify(this.modelValue)) return;
            this.$emit('update:model-value', selections);
        },

        remove(index) {
            this.update([
                ...this.modelValue.slice(0, index),
                ...this.modelValue.slice(index + 1),
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
                mirror: { constrainDimensions: true, xAxis: false, appendTo: 'body' },
                swapAnimation: { vertical: true },
                plugins: [Plugins.SwapAnimation],
            }).on('drag:start', e => {
                this.modelValue.length === 1 ? e.cancel() : this.$emit('focus');
            }).on('drag:stop', e => {
                this.$emit('blur');
            }).on('sortable:stop', e => {
                const val = [...this.modelValue];
                val.splice(e.newIndex, 0, val.splice(e.oldIndex, 1)[0]);
                this.update(val);
            });
        },

        itemCreated(item) {
            this.$emit('item-data-updated', [...this.data, item]);
            this.update([...this.modelValue, item.id]);
        },

        selectFieldSelected(selectedItemData) {
            this.$emit('item-data-updated', selectedItemData);
            this.update(selectedItemData.map(item => item.id));
        },

        setLoadingProgress(state) {
            this.$progress.loading(`relationship-fieldtype-${this.$.uid}`, state);
        }

    }

}
</script>
