<script setup>
const props = defineProps({
    text: { type: String, required: false },
});
</script>

<template>
    <div
        :class="[
            'flex items-center justify-center',
            'shadow-ui-sm disabled:shadow-none',
            'border border-gray-300 bg-gray-50 text-gray-600 antialiased dark:border-white/10 dark:bg-gray-950 dark:text-gray-300',
            'h-10 rounded-s-lg px-3 text-sm leading-[1.375rem]',
        ]"
        data-ui-input-group-prepend
    >
        <span v-if="text" class="text-gray-500 dark:text-gray-600">{{ text }}</span>
        <slot v-else />
    </div>
</template>
