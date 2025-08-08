<script setup>
import { useSlots } from 'vue';
import { cva } from 'cva';
import { Icon } from '@statamic/ui';

defineProps({
    href: { type: String, default: null },
    icon: { type: String, default: null },
    text: { type: String, default: null },
});

const slots = useSlots();
const usingSlot = !!slots.default;

const footerClasses = cva({
    base: 'text-gray-600 antialiased py-2 px-3 text-sm rounded-b-xl group/footer',
    variant: {
        noSlot: 'flex items-center gap-2',
    },
});
</script>

<template>
    <footer :class="footerClasses({ noSlot: !usingSlot })" data-ui-context-footer>
        <slot v-if="usingSlot" />
        <component v-else :is="href ? 'a' : 'div'" :href="href" class="flex items-center gap-2">
            <div
                v-if="icon"
                class="flex size-6 items-center justify-center rounded-lg bg-gray-100 p-1 text-gray-700 dark:bg-gray-900 dark:text-gray-500"
            >
                <Icon :name="icon" />
            </div>
            <div
                class="grow truncate text-sm text-gray-600 antialiased group-hover/footer:text-gray-950 dark:text-gray-400 dark:group-hover/footer:text-gray-200"
            >
                {{ text }}
            </div>
        </component>
    </footer>
</template>
