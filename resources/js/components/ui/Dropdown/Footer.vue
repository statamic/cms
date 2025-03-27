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
    <footer
        class="group/footer flex cursor-pointer items-center gap-2 rounded-b-xl px-3 py-2 text-sm text-gray-600 antialiased"
        data-ui-dropdown-footer
    >
        <div class="flex items-center gap-2">
            <div
                v-if="icon"
                class="flex size-6 items-center justify-center rounded-lg bg-gray-100 p-1 text-gray-700 dark:bg-gray-900 dark:text-gray-500"
            >
                <ui-icon :name="icon" />
            </div>
            <div
                class="grow truncate text-sm text-gray-600 antialiased group-hover/footer:text-gray-950 dark:text-gray-400 dark:group-hover/footer:text-gray-200"
            >
                <slot v-if="hasDefaultSlot" />
                <template v-else>{{ text }}</template>
            </div>
        </div>
    </footer>
</template>
