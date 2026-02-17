<script setup>
import { cva } from 'cva';
import CharacterCounter from './CharacterCounter.vue';
import Button from './Button/Button.vue';
import autosize from 'autosize/dist/autosize.js';
import { computed, nextTick, onBeforeUnmount, onMounted, useTemplateRef } from 'vue';
import useCopy from '@/composables/copy';

defineEmits(['update:modelValue']);

const props = defineProps({
    /** When `true`, the textarea will automatically grow/shrink to fit content */
    elastic: { type: Boolean, default: false },
    /** When `true`, shows a copy button to copy the value to clipboard */
    copyable: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
    /** ID attribute for the textarea element */
    id: { type: String, default: null },
    readOnly: { type: Boolean, default: false },
    required: { type: Boolean, default: false },
    /** Controls resize behavior. Options: `both`, `horizontal`, `vertical`, `none` */
    resize: { type: String, default: 'vertical' },
    /** Number of visible text rows */
    rows: { type: [Number, String], default: 4 },
    /** The controlled value of the textarea */
    modelValue: { type: String, default: null },
    /** Specify a character limit */
    limit: { type: Number, default: null },
});

const { copySupported, copied, copy } = useCopy();
const canCopy = computed(() => props.copyable && copySupported.value);

const classes = cva({
    base: [
        'w-full block bg-white dark:bg-gray-900 px-3 pt-2.5 pb-3 rounded-lg',
        'border border-gray-300 with-contrast:border-gray-500 dark:border-gray-700',
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

const textarea = useTemplateRef('textarea');

onMounted(() => {
    if (props.elastic) {
        autosize(textarea.value);
        setTimeout(() => nextTick(() => autosize.update(textarea.value)), 1);
    }
});

onBeforeUnmount(() => {
    if (props.elastic) {
        autosize.destroy(textarea.value);
    }
});
</script>

<template>
    <div class="group/input relative block w-full" data-ui-input>
        <textarea
            ref="textarea"
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
        <div class="absolute right-2 top-2" v-if="canCopy">
            <Button
                size="sm"
                :icon="copied ? 'clipboard-check' : 'clipboard'"
                variant="subtle"
                @click="copy(modelValue)"
                class="animate"
                :class="copied ? 'animate-wiggle' : ''"
            />
        </div>
        <div class="absolute right-2 bottom-2" v-if="limit">
            <CharacterCounter :text="modelValue" :limit />
        </div>
    </div>
</template>
