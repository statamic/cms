<script setup>
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

const isRadio = props.control === 'radio';
const chipClass = isRadio
    ? 'rounded-full px-2 py-1'
    : 'items-center rounded-lg px-2 py-1';
</script>

<template>
    <div
        class="pointer-events-none select-none"
        :class="{
            'flex flex-col gap-1.5 pb-0.75': appearance === 'default',
            'flex flex-wrap gap-1.5': appearance === 'inline' || appearance === 'chips',
        }"
        aria-hidden="true"
    >
        <div
            v-for="selected in [true, false]"
            :key="selected ? 'on' : 'off'"
            class="flex items-center gap-1"
            :class="appearance === 'chips' ? `border border-gray-300 shadow-ui-xs dark:border-gray-700 bg-white dark:bg-gray-500 ${chipClass}` : null"
        >
            <div
                class="shrink-0 border border-gray-400/75 dark:border-gray-500"
                :class="[
                    isRadio ? 'size-2 rounded-full' : 'size-2 rounded-sm',
                    selected ? 'border-ui-accent-bg bg-ui-accent-bg/15 dark:bg-gray-300/30' : 'bg-white dark:bg-gray-500',
                ]"
            />
            <div
                class="h-1 rounded-sm bg-gray-300 dark:bg-gray-600"
                :class="selected ? 'w-5' : 'w-4'"
            />
        </div>
    </div>
</template>
