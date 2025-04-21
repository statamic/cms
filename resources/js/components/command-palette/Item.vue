<script setup>
import { useSlots, shallowRef, onMounted } from 'vue';

const props = defineProps({
    href: { type: String, default: null },
    icon: { type: String, default: null },
    text: { type: String, default: null },
    badge: { type: String, default: null },
});

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const iconComponent = shallowRef(null);

onMounted(() => {
    if (props.icon) {
        iconComponent.value = true;
    }
});

function click(event) {
    if (props.href) return;

    event.preventDefault();

    // TODO: Handle ctrl/cmd + enter key to open item in new browser tab, just like ctrl/cms + click does
}
</script>

<template>
    <a
        :class="[
            'flex items-center gap-2 border-0',
            'rounded-lg px-2 py-1.5 text-sm antialiased',
            'text-gray-700 dark:text-gray-300',
            'not-data-disabled:cursor-pointer data-disabled:opacity-50',
            'data-highlighted:bg-gray-200/70 dark:data-highlighted:bg-gray-900/70',
            'outline-hidden focus-visible:bg-gray-100 dark:focus-visible:bg-gray-900',
        ]"
        data-command-palette-item
        :as="href ? 'a' : 'div'"
        :href="href"
        @click="click"
    >
        <div v-if="icon" class="flex size-6 items-center justify-center p-1 text-gray-500">
            <ui-icon v-if="iconComponent" :name="icon" class="size-4" :key="icon" />
            <div v-else class="size-4 shrink-0" />
        </div>
        <div class="flex-1">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
        <ui-badge v-if="badge" :text="badge" variant="flat" />
    </a>
</template>
