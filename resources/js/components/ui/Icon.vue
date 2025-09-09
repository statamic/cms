<script setup>
import { computed, defineAsyncComponent, shallowRef, watch } from 'vue';

const icons = import.meta.glob('../../../svg/icons/*.svg');

const props = defineProps({
    name: { type: String, required: true },
});

const icon = shallowRef(null);

const customIcon = computed(() => {
    if (! props.name.includes('/')) {
        return null;
    }

    const manifest = Statamic.$config.get('customSvgIcons');
    const [iconSet, icon] = props.name.split('/');

    // If there's no key in the manifest with the set name, return null.
    if (!manifest[iconSet]) {
        return null;
    }

    return manifest[iconSet][icon] || null;
});

const loadIcon = () => {
    // When the icon is an SVG string, return it as a component
    if (props.name.startsWith('<svg')) {
        return defineAsyncComponent(() => {
            return new Promise((resolve) => resolve({ template: props.name }));
        });
    }

    // When it's a custom icon, return it as a component
    if (customIcon.value) {
        return defineAsyncComponent(() => {
            return new Promise(resolve => resolve({ template: customIcon.value }));
        });
    }

    const icon = icons[`../../../svg/icons/${props.name}.svg`];

    if (!icon) {
        console.warn(`Icon not found: ${props.name}`);
        return null;
    }

    return defineAsyncComponent(() => icon());
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
