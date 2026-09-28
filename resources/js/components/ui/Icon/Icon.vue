<script setup>
import { computed, ref, watch } from 'vue';
import { getIconSet } from './registry.js';
import DOMPurify from 'dompurify';

const props = defineProps({
    /** Icon name */
    name: { type: String, required: true },
    /** Name of the icon set */
    set: { type: String, default: 'default' },
});

const svgContent = ref('');
const iconComponent = computed(() => ({ template: svgContent.value }));
const delimiter = '::';

const icon = computed(() => {
    const delimiterIndex = props.name.indexOf(delimiter);
    const hasSetInName = delimiterIndex > 0 && delimiterIndex < props.name.length - delimiter.length;

    if (! hasSetInName) {
        return { name: props.name, set: props.set, setFromName: null };
    }

    const set = props.name.substring(0, delimiterIndex);
    const name = props.name.substring(delimiterIndex + delimiter.length);

    return { name, set, setFromName: set };
});

const loadIcon = async () => {
    if (props.name.startsWith('<svg')) {
        svgContent.value = DOMPurify.sanitize(props.name);
        return;
    }

    const { name, set, setFromName } = icon.value;

    if (setFromName && props.set !== 'default' && props.set !== setFromName) {
        console.warn(`Icon name [${props.name}] includes set [${setFromName}], ignoring set prop [${props.set}]`);
    }

    const iconSet = getIconSet(set);

    if (!iconSet) {
        console.warn(`Icon set [${set}] not registered`);
        svgContent.value = ''
        return
    }

    let rawSvg = '';

    if (iconSet.type === 'strings') {
        rawSvg = loadFromStringSet(iconSet.data, name);
    } else if (iconSet.type === 'glob') {
        rawSvg = await loadFromGlobSet(iconSet.data, name);
    }

    if (!rawSvg) {
        console.warn(set === 'default'
            ? `Icon [${name}] not found`
            : `Icon [${name}] not found in set [${set}]`);
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
