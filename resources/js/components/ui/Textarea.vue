<script setup>
import { cva } from 'cva';
import { CharacterCounter } from '@statamic/ui';

defineEmits(['update:modelValue']);

const props = defineProps({
    description: { type: String, default: null },
    elastic: { type: Boolean, default: false },
    label: { type: String, default: null },
    required: { type: Boolean, default: false },
    resize: { type: String, default: 'vertical' },
    rows: { type: [Number, String], default: 4 },
    modelValue: { type: String, default: null },
    limit: { type: Number, default: null },
});

const classes = cva({
    base: [
        'w-full block bg-white dark:bg-gray-900 px-3 pt-2.5 pb-3 rounded-lg',
        'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
        'text-gray-600 dark:text-gray-300',
        'appearance-none antialiased shadow-ui-sm disabled:shadow-none not-prose',
        'focus:focus-outline'
    ],
    variants: {
        resize: {
            both: 'resize',
            horizontal: 'resize-x',
            vertical: 'resize-y',
            none: 'resize-none',
        },
        elastic: {
            true: 'field-sizing-content',
        },
    },
})({ ...props });
</script>

<template>
    <div class="group/input relative block w-full" data-ui-input>
        <textarea
            :class="classes"
            :rows="rows"
            v-bind="$attrs"
            :value="modelValue"
            data-ui-control
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <div class="absolute right-2 bottom-2" v-if="limit">
            <CharacterCounter :text="modelValue" :limit />
        </div>
    </div>
</template>
