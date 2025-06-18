<script setup>
defineProps({
    text: { type: String, default: null },
    name: { type: String, default: null },
    active: { type: Boolean, default: false },
});
</script>

<template>
    <button
        :data-state="active ? 'active' : null"
        :value="name"
        class="translate-y-px cursor-pointer p-2 pb-1 hover:text-gray-600 data-[state=active]:border-b data-[state=active]:border-gray-800 data-[state=active]:text-gray-800 dark:hover:text-gray-400 dark:data-[state=active]:border-gray-200 dark:data-[state=active]:text-gray-200"
    >
        <slot>{{ text }}</slot>
    </button>
</template>
