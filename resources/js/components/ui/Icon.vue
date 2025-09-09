<script setup>
import { computed, ref, watch } from 'vue';

const icons = import.meta.glob('../../../svg/icons/*.svg', { query: '?raw', import: 'default' });

const props = defineProps({
    name: { type: String, required: true },
});

const svgContent = ref('');

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

const loadIcon = async () => {
    // When the icon is an SVG string, use it directly
    if (props.name.startsWith('<svg')) {
        svgContent.value = props.name;
        return;
    }

    // When it's a custom icon, use it directly
    if (customIcon.value) {
        svgContent.value = customIcon.value;
        return;
    }

    const icon = icons[`../../../svg/icons/${props.name}.svg`];

    if (!icon) {
        console.warn(`Icon not found: ${props.name}`);
        svgContent.value = '';
        return;
    }

    try {
        svgContent.value = await icon();
    } catch (error) {
        console.warn(`Failed to load icon: ${props.name}`, error);
        svgContent.value = '';
    }
};

watch(
    () => props.name,
    () => loadIcon(),
    { immediate: true },
);
</script>

<template>
    <svg
        v-if="svgContent"
        v-html="svgContent"
        :class="['size-4 shrink-0']"
        v-bind="$attrs"
    />
</template>
