<script setup>
// Almost definitely a throwaway component
import { defineAsyncComponent, shallowRef, watch } from 'vue';

const props = defineProps({
    name: {
        type: String,
        required: true,
    },
});

const icon = shallowRef(null);

const loadIcon = () => {
    // Handle direct SVG strings
    if (props.name.startsWith('<svg')) {
        return defineAsyncComponent(() => {
            return new Promise((resolve) => resolve({ template: props.name }));
        });
    }

    // Handle file imports
    return defineAsyncComponent(() => import(`./svg/${props.name}.svg`));
};

watch(
    () => props.name,
    () => {
        icon.value = loadIcon();
    },
    { immediate: true },
);
</script>

<template>
    <component :is="icon" :class="['size-4 shrink-0']" v-bind="$attrs" />
</template>
