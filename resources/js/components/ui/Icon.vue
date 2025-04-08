<script setup>
// Almost definitely a throwaway component
import { defineAsyncComponent, shallowRef, watch } from 'vue';
import { preloadIcon } from './iconCache';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    preload: {
        type: Boolean,
        default: false
    }
});

const icon = shallowRef(null);

const loadIcon = () => {
    // Ensure the icon is in the cache
    preloadIcon(props.name);

    // Create a stable component instance for this icon
    return defineAsyncComponent({
        loader: () => preloadIcon(props.name),
        suspensible: false,
        loadingComponent: {
            template: '<div class="size-4 shrink-0" />'
        }
    });
};

watch(
    () => props.name,
    () => {
        icon.value = loadIcon();
    },
    { immediate: true }
);

// If preload is true, trigger the load immediately
if (props.preload) {
    preloadIcon(props.name);
}
</script>

<template>
    <component :is="icon" :class="['size-4 shrink-0']" v-bind="$attrs" />
</template>
