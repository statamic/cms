<template>
    <div>
        <CheckboxGroup v-model="values" :inline="config.inline" ref="checkboxes">
            <Checkbox
                v-for="(option, index) in options"
                :key="index"
                :label="option.label || option.value"
                :value="option.value"
                :disabled="isReadOnly"
            />
        </CheckboxGroup>
    </div>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import CheckboxGroup from '@statamic/components/ui/Checkbox/Group.vue';
import Checkbox from '@statamic/components/ui/Checkbox/Item.vue';

export default {
    mixins: [Fieldtype, HasInputOptions],

    components: {
        CheckboxGroup,
        Checkbox,
    },

    data() {
        return {
            values: this.value || [],
        };
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.values
                .map((value) => {
                    const option = this.options.find((o) => o.value === value);
                    return option ? option.label : value;
                })
                .join(', ');
        },
    },

    watch: {
        values(values, oldValues) {
            values = this.sortValues(values);

            if (JSON.stringify(values) === JSON.stringify(oldValues)) return;

            this.update(values);
        },

        value(value) {
            this.values = this.sortValues(value);
        },
    },

    methods: {
        focus() {
            this.$refs.checkboxes.focus();
        },

        sortValues(values) {
            if (!values) return [];

            return this.options.filter((opt) => values.includes(opt.value)).map((opt) => opt.value);
        },
    },
};
</script>
