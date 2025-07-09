<script setup>
import { cva } from 'cva';
import { computed } from 'vue';
import { Description, Label, Card } from '@statamic/components/ui/index.js';
import markdown from '@statamic/util/markdown.js';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps({
    variant: {
        type: String,
        default: 'block',
    },
    label: {
        type: String,
    },
    id: {
        type: String,
    },
    instructions: {
        type: String,
        default: '',
    },
    instructionsBelow: {
        type: Boolean,
        default: false,
    },
    required: {
        type: Boolean,
        default: false,
    },
    badge: {
        type: String,
        default: '',
    },
    error: {
        type: String,
    },
    errors: {
        type: Object,
        default: (props) => (props.error ? [props.error] : []),
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    as: {
        type: String,
        default: 'div',
        validator: (value) => ['div', 'card'].includes(value),
    }
});

const labelProps = computed(() => ({
    text: props.label,
    for: props.id,
    required: props.required,
    badge: props.badge,
}));

const classes = computed(() =>
    cva({
        base: [
            'min-w-0',
        ],
        variants: {
            variant: {
                block: 'w-full',
                inline: [
                    'flex justify-between gap-x-3 gap-y-1.5',
                    'has-[[data-ui-label]~[data-ui-control]]:grid-cols-[1fr_auto]',
                    'has-[[data-ui-control]~[data-ui-label]]:grid-cols-[auto_1fr]',
                    '[&>[data-ui-control]~[data-ui-description]]:row-start-2 [&>[data-ui-control]~[data-ui-description]]:col-start-2',
                    '[&>[data-ui-label]~[data-ui-control]]:row-start-1 [&>[data-ui-label]~[data-ui-control]]:col-start-2',
                ],
            },
            disabled: {
                true: 'opacity-50',
            },
        },
    })({ ...props }),
);

const instructions = computed(() => props.instructions ? markdown(props.instructions, { openLinksInNewTabs: true }) : null);
const wrapperComponent = computed(() => props.as === 'card' ? Card : 'div');
</script>

<template>
    <component :is="wrapperComponent" :class="[classes, $attrs.class]" data-ui-input-group>
        <div v-if="$slots.actions" class="mb-2 flex items-center justify-between gap-x-1 h-6">
            <slot name="label">
                <Label v-if="label" v-bind="labelProps" class="flex-1" />
            </slot>
            <slot name="actions" />
        </div>
        <!-- =Jay. Only add bottom margins if there is no switch that follows, because the layout of the switch is with flexbox to the right of the field (rather than underneath the field text).

        [&:not(:has(+[data-ui-switch]))]:mb-1 = add a bottom margin if a switch doesn't follow
        [&:has([data-ui-description]):not(:has(+[data-ui-switch]))]:mb-2.5 = add an increased bottom margin if there's a description (and a switch doesn't follow)
        -->
        <div data-ui-field-text class="[&:not(:has(+[data-ui-switch]))]:mb-1 [&:has([data-ui-description]):not(:has(+[data-ui-switch]))]:mb-2.5">
            <slot v-if="!$slots.actions" name="label">
                <Label v-if="label" v-bind="labelProps" class="flex-1" />
            </slot>
            <Description :text="instructions" class="mt-1" v-if="instructions && !instructionsBelow" />
        </div>
        <slot />
        <Description :text="instructions" v-if="instructions && instructionsBelow" class="mt-2" />
        <Description v-if="errors" v-for="(error, i) in errors" :key="i" :text="error" class="mt-2 text-red-500" />
    </component>
</template>
