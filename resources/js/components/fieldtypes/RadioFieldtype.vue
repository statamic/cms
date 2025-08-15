<template>
    <RadioGroup :inline="config.inline" :model-value="value" @update:model-value="update" ref="radio">
        <Radio
            v-for="(option, index) in options"
            :disabled="config.disabled"
            :key="index"
            :label="option.label || option.value"
            :read-only="isReadOnly"
            :value="option.value"
        />
    </RadioGroup>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { RadioGroup, Radio } from '@/components/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],

    components: {
        RadioGroup,
        Radio,
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            var option = this.options.find((o) => o.value === this.value);
            return option ? option.label : this.value;
        },
    },

    methods: {
        focus() {
            this.$refs.radio.focus();
        },
    },
};
</script>
