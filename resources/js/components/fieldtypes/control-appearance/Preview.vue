<script setup>
import { computed } from 'vue';

const props = defineProps({
    appearance: {
        type: String,
        required: true,
        validator: (value) => ['default', 'inline', 'chips'].includes(value),
    },
    control: {
        type: String,
        default: 'radio',
        validator: (value) => ['radio', 'checkbox'].includes(value),
    },
});

const inline = computed(() => props.appearance === 'inline' || props.appearance === 'chips');

const isRadio = computed(() => props.control === 'radio');
const chipClass = computed(() => isRadio.value
    ? 'border border-gray-300 dark:border-gray-700 shadow-ui-xs rounded-full px-1.5 pe-2 py-1 bg-white dark:bg-gray-850'
    : 'border border-gray-300 dark:border-gray-700 shadow-ui-xs items-center rounded-sm px-1.5 pe-2 py-1 bg-white dark:bg-gray-850',
);
</script>

<template>
    <div
        class="pointer-events-none select-none"
        :class="{
            'flex flex-col gap-1.5 pb-0.75': appearance === 'default',
            'flex flex-wrap gap-1.5': inline,
        }"
        aria-hidden="true"
    >
        <div
            v-for="selected in [true, false]"
            :key="selected ? 'on' : 'off'"
            class="flex items-center gap-1"
            :class="appearance === 'chips' ? chipClass : null"
        >
            <div
                v-if="isRadio"
                class="relative flex size-2 shrink-0 items-center justify-center rounded-full border shadow-ui-xs"
                :class="
                    selected
                        ? 'border-ui-accent-bg bg-white dark:bg-gray-300'
                        : 'border-gray-400/75 bg-white dark:border-none dark:bg-gray-500'
                "
            >
                <div v-if="selected" class="size-1 rounded-full bg-ui-accent-bg" />
            </div>
            <div
                v-else
                class="flex size-2 shrink-0 items-center justify-center rounded-[2px] border shadow-ui-xs"
                :class="
                    selected
                        ? 'border-ui-accent-bg bg-ui-accent-bg dark:border-none'
                        : 'border-gray-400/75 bg-white dark:border-none dark:bg-gray-500'
                "
            >
                <svg
                    v-if="selected"
                    viewBox="0 0 10 8"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    class="size-1.5 shrink-0 !text-white !opacity-100"
                    aria-hidden="true"
                >
                    <path
                        d="M9 1L3.5 6.5L1 4"
                        stroke="currentColor"
                        stroke-width="1.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
            </div>
            <div
                class="h-1 rounded-[2px] bg-gray-300 dark:bg-gray-600"
                :class="[selected ? 'w-5 dark:bg-gray-500' : 'w-4 dark:bg-gray-700']"
            />
        </div>
    </div>
</template>
