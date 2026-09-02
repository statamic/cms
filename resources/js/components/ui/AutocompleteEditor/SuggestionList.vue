<template>
    <div
        class="autocomplete-suggestions min-w-52 max-w-xs overflow-hidden rounded-xl border border-gray-200 bg-white text-sm shadow-lg dark:border-white/10 dark:bg-gray-800"
    >
        <div v-if="items.length" class="st-custom-scrollbar max-h-64 overflow-auto p-1.5">
            <button
                v-for="(item, index) in items"
                :key="item.value"
                type="button"
                class="flex w-full cursor-pointer items-center rounded-lg px-2 py-1.5 text-start text-gray-700 dark:text-gray-300"
                :class="{ 'bg-gray-100 dark:bg-gray-900': index === selectedIndex }"
                @mouseenter="selectedIndex = index"
                @mousedown.prevent
                @click="selectItem(index)"
            >
                <span class="line-clamp-1">{{ item.label ?? item.value }}</span>
            </button>
        </div>
        <div v-else class="p-3 text-center text-xs text-gray-600 dark:text-gray-400">
            {{ __('No results') }}
        </div>
    </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { __ } from '@/bootstrap/globals';

const props = defineProps({
    items: { type: Array, default: () => [] },
    command: { type: Function, required: true },
});

const selectedIndex = ref(0);

watch(
    () => props.items,
    () => (selectedIndex.value = 0),
);

function selectItem(index) {
    const item = props.items[index];
    if (item) props.command(item);
}

function onKeyDown({ event }) {
    if (!props.items.length) return false;

    if (event.key === 'ArrowUp') {
        selectedIndex.value = (selectedIndex.value + props.items.length - 1) % props.items.length;
        return true;
    }

    if (event.key === 'ArrowDown') {
        selectedIndex.value = (selectedIndex.value + 1) % props.items.length;
        return true;
    }

    if (event.key === 'Enter') {
        selectItem(selectedIndex.value);
        return true;
    }

    return false;
}

defineExpose({ onKeyDown });
</script>
