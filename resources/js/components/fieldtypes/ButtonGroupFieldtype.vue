<template>
    <ButtonGroup :overflow="config.appearance_previews ? 'gap' : 'stack'" ref="buttonGroup">
        <template v-for="(option, $index) in options" :key="$index">
            <Button
                v-if="config.appearance_previews"
                ref="button"
                :disabled="config.disabled"
                :name="name"
                :read-only="isReadOnly"
                :value="option.value"
                :variant="value == option.value ? 'pressed' : 'default'"
                :class="appearancePreviewButtonClass(option.value)"
                @click="updateSelectedOption(option.value)"
            >
                <div class="flex w-full flex-col items-start gap-2 py-0.5 text-left">
                    <span class="text-sm font-medium">{{ option.label || option.value }}</span>
                    <ControlAppearancePreview :appearance="option.value" :control="config.control || 'radio'" />
                </div>
            </Button>
            <Button
                v-else
                ref="button"
                :disabled="config.disabled"
                :name="name"
                :read-only="isReadOnly"
                :text="option.label || option.value"
                :value="option.value"
                :variant="value == option.value ? 'pressed' : 'default'"
                @click="updateSelectedOption(option.value)"
            />
        </template>
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
        appearancePreviewButtonClass(optionValue) {
            if (!this.config.appearance_previews) {
                return null;
            }

            const base = 'min-w-34 h-auto items-start justify-start py-1.5';

            if (this.value != optionValue) {
                return [
                    base,
                    'from-white to-white hover:from-white hover:to-gray-50',
                    'dark:from-gray-850 dark:to-gray-850 dark:hover:from-gray-800 dark:hover:to-gray-850',
                ];
            }

            return [
                base,
                'from-gray-150 to-gray-50 border-gray-300',
                'dark:from-gray-925 dark:to-gray-900 dark:border-gray-700/80',
            ];
        },

        updateSelectedOption(newValue) {
            this.update(this.value == newValue && this.config.clearable ? null : newValue);
        },

        focus() {
            this.$refs.button[0].focus();
        },
    },
};
</script>
