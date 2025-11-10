<script setup>
import { useSlots, shallowRef, onMounted, computed } from 'vue';
import { Icon, Badge } from '@/components/ui';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: { type: String, default: null },
    openNewTab: { type: Boolean, default: false },
    icon: { type: String, default: null },
    text: { type: String, default: null },
    badge: { type: String, default: null },
    keys: { type: Array, default: null },
    removable: { type: Boolean, default: false },
});

defineEmits(['remove']);

const slots = useSlots();
const hasDefaultSlot = !!slots.default;
const iconComponent = shallowRef(null);

const component = computed(() => {
    if (!props.href) return 'div';
    if (! props.href.startsWith('http')) return Link;
    const hostOfCurrentUrl = window.location.host;
    const hostOfHref = (new URL(props.href)).host;
    return hostOfHref === hostOfCurrentUrl ? Link : 'a';
});

onMounted(() => {
    if (props.icon) {
        iconComponent.value = true;
    }
});

function click(event) {
    if (props.href) return;

    event.preventDefault();
}
</script>

<template>
    <Component
        :is="component"
        :class="[
            'flex items-center gap-2 border-0',
            'rounded-lg px-2 py-1.5 text-sm antialiased',
            'text-gray-700 dark:text-gray-300',
            'not-data-disabled:cursor-pointer data-disabled:opacity-50',
            'data-highlighted:bg-gray-200/70 dark:data-highlighted:bg-gray-900/70',
            'outline-hidden focus-visible:bg-gray-100 dark:focus-visible:bg-gray-900',
        ]"
        data-command-palette-item
        :href="href"
        :target="openNewTab ? '_blank' : '_self'"
        @click="click"
    >
        <div v-if="icon" class="flex size-6 items-center justify-center p-1 text-gray-500">
            <Icon v-if="iconComponent" :name="icon" class="size-4" :key="icon" />
            <div v-else class="size-4 shrink-0" />
        </div>
        <div class="flex-1">
            <slot v-if="hasDefaultSlot" />
            <template v-else>{{ text }}</template>
        </div>
        <Badge v-if="badge" :text="badge" />
        <Badge v-if="keys" v-for="key in keys" :key="key" :text="key" />
        <Icon
            v-if="removable"
            name="x"
            class="size-4 opacity-30 hover:opacity-70"
            @click.prevent.stop="$emit('remove', href)"
        />
    </Component>
</template>
