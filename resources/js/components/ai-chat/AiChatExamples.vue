<script setup>
import { useTemplateRef } from 'vue';
import { Icon } from '@ui';

defineProps({
    examples: { type: Array, required: true },
    /** Pad the whole list, for when it sits in a box of its own (the floating popup). */
    padded: { type: Boolean, default: false },
});

const emit = defineEmits(['pick', 'exit']);

const list = useTemplateRef('list');
const cards = () => [...(list.value?.querySelectorAll('button') ?? [])];

const focusFirst = () => cards()[0]?.focus();
const focusLast = () => cards().at(-1)?.focus();

// Up and down move between the cards. Down from the last one hands focus back to the input.
function onKeydown(event) {
    if (event.key !== 'ArrowDown' && event.key !== 'ArrowUp') return;

    const all = cards();
    const index = all.indexOf(document.activeElement);
    if (index < 0) return;

    event.preventDefault();
    const next = event.key === 'ArrowDown' ? index + 1 : index - 1;

    if (next >= all.length) emit('exit');
    else if (next >= 0) all[next].focus();
}

defineExpose({ focusFirst, focusLast });
</script>

<template>
    <div :class="['space-y-2', padded ? 'p-2' : '']">
        <p class="px-1 text-sm tracking-tight text-gray-600 dark:text-gray-400">{{ __('Try asking') }}</p>

        <div ref="list" class="flex flex-col gap-2.5" @keydown="onKeydown">
            <!-- Styled like the CP's Card: white, a 1px ring, rounded-xl. -->
            <button
                v-for="example in examples"
                :key="example.text"
                type="button"
                class="group flex w-full cursor-pointer items-start gap-2.5 rounded-xl bg-white p-3 text-start text-sm/5 text-gray-900 ring ring-gray-200 transition-colors hover:bg-gray-50 hover:ring-gray-300 focus-visible:focus-outline dark:bg-gray-850 dark:text-gray-300 dark:ring-gray-700/80 dark:hover:bg-gray-800 dark:hover:ring-gray-600"
                @click="$emit('pick', example.text)"
            >
                <Icon :name="example.icon" class="mt-0.5 size-4 shrink-0 text-gray-600 group-hover:text-gray-900 dark:text-gray-400 dark:group-hover:text-gray-200" />
                <span class="flex-1">{{ example.text }}</span>
                <Icon name="arrow-right" class="mt-0.5 size-3.5 shrink-0 text-gray-600 opacity-0 transition-opacity group-hover:opacity-100 dark:text-gray-400 rtl:rotate-180" />
            </button>
        </div>
    </div>
</template>
