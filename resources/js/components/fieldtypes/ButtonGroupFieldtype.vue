<template>
    <ButtonGroup orientation="auto" ref="buttonGroup">
        <Button
            v-for="(option, $index) in options"
            ref="button"
            :disabled="config.disabled"
            :key="$index"
            :name="name"
            :read-only="isReadOnly"
            :text="option.label || option.value"
            :value="option.value"
            :variant="value == option.value ? 'pressed' : 'default'"
            @click="updateSelectedOption(option.value)"
        />
    </ButtonGroup>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import { Button, ButtonGroup } from '@/components/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],
    components: {
        Button,
        ButtonGroup
    },

    computed: {
        options() {
            return this.normalizeInputOptions(this.meta.options || this.config.options);
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            var option = this.options.find((o) => o.value === this.value);
            return option ? option.label : this.value;
        },
    },

    methods: {
        updateSelectedOption(newValue) {
            this.update(this.value == newValue && this.config.clearable ? null : newValue);
        },

        focus() {
            this.$refs.button[0].focus();
        },
    },
};
</script>
