<template>
    <component v-if="icon" :is="icon" />
</template>

<script setup>
import { defineAsyncComponent, shallowRef, computed, watch } from 'vue';
import { data_get } from '../bootstrap/globals.js';

const props = defineProps({
    name: String,
    default: String,
    directory: String,
});

const icon = shallowRef(null);

const customIcon = computed(() => {
    if (!props.directory) return;

    let directory = props.directory;
    let folder = null;
    let file = props.name;

    if (props.name.includes('/')) {
        [folder, file] = props.name.split('/');
        directory = directory + '/' + folder;
    }

    let svgIcons = this.$config.get('customSvgIcons')[directory] ?? [];

    return svgIcons[file] ?? null;
});

const evaluateIcon = () => {
    if (customIcon.value) {
        return defineAsyncComponent(() => {
            return new Promise((resolve) => resolve({ template: customIcon.value }));
        });
    }

    if (props.name.startsWith('<svg')) {
        return defineAsyncComponent(() => {
            return new Promise((resolve) => resolve({ template: props.name }));
        });
    }

    return defineAsyncComponent(() => {
        const [set, file] = splitIcon(props.name);

        return import(`./../../svg/icons/${set}/${file}.svg`).catch((e) => {
            if (!props.default) return fallbackIconImport();
            const [set, file] = splitIcon(props.default);
            return import(`./../../svg/icons/${set}/${file}.svg`).catch((e) => fallbackIconImport());
        });
    });
};

const splitIcon = (icon) => {
    if (!icon.includes('/')) icon = 'regular/' + icon;

    return icon.split('/');
};

const fallbackIconImport = () => {
    return import('./../../svg/icons/regular/image.svg');
};

watch(
    () => props.name,
    () => {
        icon.value = evaluateIcon();
    },
    { immediate: true },
);
</script>
