<script setup>
import { useSlots } from 'vue';

defineProps({
    icon: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
</script>

<template>
    <footer class="flex items-center cursor-pointer text-gray-600 antialiased gap-2 py-2 px-3 text-sm rounded-b-xl group/footer" data-ui-dropdown-footer>
        <div class="flex items-center gap-2">
            <div v-if="icon" class="size-6 flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-900 p-1 text-gray-700 dark:text-gray-500">
                <ui-icon :name="icon" />
            </div>
            <div class="grow truncate text-sm text-gray-600 dark:text-gray-400 group-hover/footer:text-gray-950 dark:group-hover/footer:text-gray-200 antialiased">
                <slot v-if="hasDefaultSlot" />
                <template v-else>{{ text }}</template>
            </div>
        </div>
    </footer>
</template>
