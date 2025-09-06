<script setup>
import { computed, defineAsyncComponent, shallowRef, watch } from 'vue';

// Import all icons from the icons and ui directories (lazy loaded)
const icons = import.meta.glob('../../../svg/icons/*.svg');
const uiIcons = import.meta.glob('../../../svg/ui/*.svg');

const props = defineProps({
    name: { type: String, required: true },
    directory: { type: String, required: false },
});

const icon = shallowRef(null);

const customIcon = computed(() => {
    if (! props.directory) return;

    let directory = props.directory;
    let file = props.name;

    let svgIcons = Statamic.$config.get('customSvgIcons')[directory] ?? [];

    return svgIcons[file] ?? null;
});

const loadIcon = () => {
    // When it's a custom icon, return it as a component
    if (customIcon.value) {
        return defineAsyncComponent(() => {
            return new Promise(resolve => resolve({ template: customIcon.value }));
        });
    }

    // When the icon is an SVG string, return it as a component
    if (props.name.startsWith('<svg')) {
        return defineAsyncComponent(() => {
            return new Promise((resolve) => resolve({ template: props.name }));
        });
    }

    // Find the icon in either the icons or ui directory
    const iconPath = props.name.includes('/')
        ? `../../../svg/ui/${props.name.split('/')[1]}.svg`
        : `../../../svg/icons/${props.name}.svg`;

    const iconLoader = props.name.includes('/')
        ? uiIcons[iconPath]
        : icons[`../../../svg/icons/${props.name}.svg`];

    if (!iconLoader) {
        console.warn(`Icon not found: ${props.name}`);
        return null;
    }

    return defineAsyncComponent(() => iconLoader());
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
