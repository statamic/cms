<template>
    <div class="flex flex-col w-full">
        <Combobox
            :options="options"
            :clearable="config.clearable"
            :placeholder="__(config.placeholder)"
            :multiple="config.multiple"
            :searchable="config.searchable || config.taggable"
            :taggable="config.taggable"
            :disabled="config.disabled || isReadOnly || (config.multiple && limitReached)"
            :label-html="config.label_html"
            :model-value="value"
            @update:modelValue="comboboxUpdated"
        />

        <div class="mt-3 text-xs ltr:ml-2 rtl:mr-2" :class="limitIndicatorColor" v-if="config.max_items">
            <span v-text="currentLength"></span>/<span v-text="config.max_items"></span>
        </div>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { SortableList } from '../sortable/Sortable';
import PositionsSelectOptions from '../../mixins/PositionsSelectOptions';
import { Badge, Combobox } from '@statamic/ui';

export default {
    mixins: [Fieldtype, HasInputOptions, PositionsSelectOptions],

    components: {
        Badge,
        Combobox,
        SortableList,
    },

    computed: {
        selectedOptions() {
            let selections = this.value === null ? [] : this.value;

            if (typeof selections === 'string' || typeof selections === 'number') {
                selections = [selections];
            }

            return selections.map(value => {
                return props.options.find(option => option.value === value) ?? { label: value, value };
            });
        },

        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.selectedOptions.map((option) => option.label).join(', ');
        },

        limitReached() {
            if (!this.config.max_items) return false;

            return this.currentLength >= this.config.max_items;
        },

        limitExceeded() {
            if (!this.config.max_items) return false;

            return this.currentLength > this.config.max_items;
        },

        currentLength() {
            if (this.value) {
                return typeof this.value == 'string' ? 1 : this.value.length;
            }

            return 0;
        },

        limitIndicatorColor() {
            if (this.limitExceeded) {
                return 'text-red-500';
            } else if (this.limitReached) {
                return 'text-green-600';
            }

            return 'text-gray';
        },
    },

    methods: {
        comboboxUpdated(value) {
            this.update(value || null);
        },
    },
};
</script>
