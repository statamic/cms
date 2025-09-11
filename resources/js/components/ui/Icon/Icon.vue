<script setup>
import { computed, ref, watch } from 'vue';
import { getIconSet } from './registry.js';

const props = defineProps({
    name: { type: String, required: true },
    set: { type: String, default: 'default' },
});

const svgContent = ref('');
const iconComponent = computed(() => ({ template: svgContent.value }));

const loadIcon = async () => {
    if (props.name.startsWith('<svg')) {
        svgContent.value = props.name;
        return;
    }

    const iconSet = getIconSet(props.set);

    if (!iconSet) {
        console.warn(`Icon set [${props.set}] not registered`);
        svgContent.value = ''
        return
    }

    let rawSvg = '';

    if (iconSet.type === 'strings') {
        rawSvg = loadFromStringSet(iconSet.data, props.name);
    } else if (iconSet.type === 'glob') {
        rawSvg = await loadFromGlobSet(iconSet.data, props.name);
    }

    if (!rawSvg) {
        console.warn(props.set === 'default'
            ? `Icon [${props.name}] not found`
            : `Icon [${props.name}] not found in set [${props.set}]`);
        svgContent.value = ''
        return
    }

    svgContent.value = rawSvg
}

const loadFromStringSet = (stringSet, iconName) => stringSet[iconName] || null;

const loadFromGlobSet = async (globSet, iconName) => {
    const svgLoader = globSet[iconName];

    if (!svgLoader) return null;

    try {
        return await svgLoader();
    } catch (error) {
        console.warn(`Failed to load icon [${iconName}]`, error);
        return null;
    }
}

watch(
    () => [props.name, props.set],
    () => loadIcon(),
    { immediate: true },
);
</script>

<template>
    <component
        v-if="svgContent"
        :is="iconComponent"
        :class="['size-4 shrink-0']"
        v-bind="$attrs"
    />
</template>
