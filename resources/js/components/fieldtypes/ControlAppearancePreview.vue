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
            :class="appearance === 'chips' ? `border border-gray-300 shadow-ui-xs dark:border-gray-700 ${chipClass}` : null"
        >
            <div
                class="relative flex shrink-0 items-center justify-center border"
                :class="[
                    isRadio ? 'size-2 rounded-full' : 'size-2 rounded-sm',
                    selected
                        ? 'border-ui-accent-bg bg-ui-accent-bg/15 dark:border-none dark:bg-gray-300'
                        : 'border-gray-400/75 bg-white dark:border-none dark:bg-gray-500',
                ]"
            >
                <div
                    v-if="selected"
                    class="bg-ui-accent-bg/10 dark:bg-ui-accent-bg"
                    :class="isRadio ? 'size-1 rounded-full' : 'size-1 rounded-sm'"
                />
            </div>
            <div
                class="h-1 rounded-sm bg-gray-300 dark:bg-gray-600"
                :class="[selected ? 'w-5 dark:bg-gray-500' : 'w-4 dark:bg-gray-700']"
            />
        </div>
    </div>
</template>
