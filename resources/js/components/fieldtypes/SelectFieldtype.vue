<template>
    <Combobox
        class="w-full"
        :options="options"
        :clearable="config.clearable"
        :placeholder="__(config.placeholder)"
        :multiple="config.multiple"
        :searchable="config.searchable || config.taggable"
        :taggable="config.taggable"
        :disabled="config.disabled || isReadOnly"
        :max-selections="config.max_items"
        :label-html="config.label_html"
        :model-value="value"
        @update:modelValue="comboboxUpdated"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { SortableList } from '../sortable/Sortable';
import { Badge, Combobox } from '@statamic/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],

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

            return selections.map((value) => {
                return props.options.find((option) => option.value === value) ?? { label: value, value };
            });
        },

        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.selectedOptions.map((option) => option.label).join(', ');
        },
    },

    methods: {
        comboboxUpdated(value) {
            this.update(value || null);
        },
    },
};
</script>
