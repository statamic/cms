<template>
    <div class="relationship-input @container" :class="{ 'relationship-input-empty': items.length == 0 }">
        <relationship-select-field
            v-if="!initializing && usesSelectField"
            :config="config"
            :items="items"
            :multiple="maxItems > 1"
            :typeahead="mode === 'typeahead'"
            :taggable="taggable"
            :max-selections="maxItems"
            :read-only="readOnly"
            :url="selectionsUrl"
            :site="site"
            @input="selectFieldSelected"
            @focus="$emit('focus')"
            @blur="$emit('blur')"
        />

        <loading-graphic v-if="initializing" :inline="true" />

        <template v-if="shouldShowSelectedItems">
            <div
                ref="items"
                class="grid grid-cols-1 gap-2 outline-hidden @xl:grid-cols-2"
                :class="{ 'mt-2': usesSelectField && items.length }"
            >
                <component
                    :is="itemComponent"
                    v-for="(item, i) in items"
                    :key="item.id"
                    :item="item"
                    :config="config"
                    :status-icon="statusIcons"
                    :editable="canEdit && (item.editable || item.editable === undefined)"
                    :sortable="!readOnly && canReorder"
                    :read-only="readOnly"
                    :form-component="formComponent"
                    :form-component-props="formComponentProps"
                    :form-stack-size="formStackSize"
                    class="related-item"
                    @removed="remove(i)"
                />
            </div>

            <div class="text-gray py-2 text-xs" v-if="maxItemsReached && maxItems != 1">
                <span>{{ __('Maximum items selected:') }}</span>
                <span>{{ maxItems }}/{{ maxItems }}</span>
            </div>
            <div
                v-if="canSelectOrCreate"
                class="relationship-input-buttons @container relative"
                :class="{ 'mt-4': items.length > 0 }"
            >
                <div class="-mb-2 flex flex-wrap items-center text-sm">
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
                    <Button
                        ref="existing"
                        icon="link"
                        variant="filled"
                        :text="__('Link Item')"
                        @click.prevent="isSelecting = true"
                    />
                </div>
            </div>

            <stack name="item-selector" v-if="isSelecting" @closed="isSelecting = false" v-slot="{ close }">
                <item-selector
                    :name="name"
                    :filters-url="filtersUrl"
                    :selections-url="selectionsUrl"
                    :site="site"
                    :initial-columns="columns"
                    :initial-sort-column="initialSortColumn"
                    :initial-sort-direction="initialSortDirection"
                    :initial-selections="value"
                    :max-selections="maxItems"
                    :search="search"
                    :exclusions="exclusions"
                    :type="config.type"
                    :tree="config.query_scopes?.length > 0 ? null : tree"
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
import { Sortable, Plugins } from '@shopify/draggable';
import RelationshipSelectField from './SelectField.vue';
import { Button } from '@statamic/ui';

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
        formStackSize: String,
        mode: {
            type: String,
            default: 'default',
        },
        taggable: Boolean,
        columns: {
            type: Array,
            default: () => [],
        },
        tree: Object,
        initialSortColumn: {
            type: String,
            default: 'title',
        },
        initialSortDirection: {
            type: String,
            default: 'asc',
        },
    },

    emits: ['input', 'focus', 'blur', 'item-data-updated', 'loading'],

    components: {
        ItemSelector,
        RelatedItem,
        CreateButton,
        RelationshipSelectField,
        Button,
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
        };
    },

    computed: {
        items() {
            if (this.value === null) return [];

            return this.value?.map((selection) => {
                const data = this.data.find((item) => item.id == selection);

                if (!data) return { id: selection, title: selection };

                return data;
            });
        },

        maxItemsReached() {
            return this.value?.length >= this.maxItems;
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
        },
    },

    mounted() {
        this.initializeData().then(() => {
            this.initializing = false;
            if (this.canReorder) {
                this.$nextTick(() => this.makeSortable());
            }
        });
    },

    beforeUnmount() {
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
            },
        },

        isSelecting(selecting) {
            this.$emit(selecting ? 'focus' : 'blur');
        },

        itemData(data, olddata) {
            if (this.initializing) return;
            this.$emit('item-data-updated', data);
        },
    },

    methods: {
        update(selections) {
            if (JSON.stringify(selections) == JSON.stringify(this.value)) return;
            this.$emit('input', selections);
        },

        remove(index) {
            this.update([...this.value.slice(0, index), ...this.value.slice(index + 1)]);
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

            return this.$axios
                .post(this.itemDataUrl, { site: this.site, selections })
                .then((response) => {
                    this.$emit('item-data-updated', response.data.data);
                })
                .finally(() => {
                    this.loading = false;
                });
        },

        makeSortable() {
            this.sortable = new Sortable(this.$refs.items, {
                draggable: '.related-item',
                handle: '.item-move',
                mirror: { constrainDimensions: true, xAxis: false, appendTo: 'body' },
                swapAnimation: { vertical: true },
                plugins: [Plugins.SwapAnimation],
            })
                .on('drag:start', (e) => {
                    this.value.length === 1 ? e.cancel() : this.$emit('focus');
                })
                .on('drag:stop', (e) => {
                    this.$emit('blur');
                })
                .on('sortable:stop', (e) => {
                    const val = [...this.value];
                    val.splice(e.newIndex, 0, val.splice(e.oldIndex, 1)[0]);
                    this.update(val);
                });
        },

        itemCreated(item) {
            this.$emit('item-data-updated', [...this.data, item]);
            this.update([...this.value, item.id]);
        },

        selectFieldSelected(selectedItemData) {
            this.$emit('item-data-updated', selectedItemData);
            this.update(selectedItemData.map((item) => item.id));
        },

        setLoadingProgress(state) {
            this.$progress.loading(`relationship-fieldtype-${this.$.uid}`, state);
        },
    },
};
</script>
