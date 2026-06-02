<template>
    <ButtonGroup :overflow="config.appearance_previews ? 'gap' : 'stack'" ref="buttonGroup">
        <Button
            v-for="(option, $index) in options"
            ref="button"
            :disabled="config.disabled"
            :key="$index"
            :name="name"
            :read-only="isReadOnly"
            :text="config.appearance_previews ? null : (option.label || option.value)"
            :value="option.value"
            :variant="value == option.value ? 'pressed' : 'default'"
            :class="config.appearance_previews ? 'min-w-36 justify-start' : null"
            @click="updateSelectedOption(option.value)"
        >
            <div v-if="config.appearance_previews" class="flex w-full flex-col items-start gap-2 py-0.5 text-left">
                <span class="text-sm font-medium">{{ option.label || option.value }}</span>
                <ControlAppearancePreview :appearance="option.value" :control="config.control || 'radio'" />
            </div>
        </Button>
    </ButtonGroup>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import HasInputOptions from './HasInputOptions.js';
import ControlAppearancePreview from './ControlAppearancePreview.vue';
import { Button, ButtonGroup } from '@/components/ui';

export default {
    mixins: [Fieldtype, HasInputOptions],
    components: {
        Button,
        ButtonGroup,
        ControlAppearancePreview,
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
