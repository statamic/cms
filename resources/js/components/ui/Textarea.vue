<script setup>
import { cva } from 'cva';
import { CharacterCounter } from '@/components/ui';

defineEmits(['update:modelValue']);

const props = defineProps({
    elastic: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    id: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    resize: { type: String, default: 'vertical' },
    rows: { type: [Number, String], default: 4 },
    modelValue: { type: String, default: null },
    limit: { type: Number, default: null },
});

const classes = cva({
    base: [
        'w-full block bg-white dark:bg-gray-900 px-3 pt-2.5 pb-3 rounded-lg',
        'border border-gray-300 with-contrast:border-gray-500 dark:border-x-0 dark:border-t-0 dark:border-white/10 dark:inset-shadow-2xs dark:inset-shadow-black',
        'text-gray-900 dark:text-gray-300',
        'appearance-none antialiased shadow-ui-sm disabled:shadow-none read-only:border-dashed not-prose'
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
            :id="id"
            v-bind="$attrs"
            :value="modelValue"
            :disabled="disabled"
            :readonly="readOnly"
            data-ui-control
            @input="$emit('update:modelValue', $event.target.value)"
        />
        <div class="absolute right-2 bottom-2" v-if="limit">
            <CharacterCounter :text="modelValue" :limit />
        </div>
    </div>
</template>
