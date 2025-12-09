<script setup lang="ts">
import Fieldtype from '@/components/fieldtypes/fieldtype';
import { ref } from 'vue';
import type { Tab } from '../themes/Selector.vue';
import Selector from '../themes/Selector.vue';
import { Theme, ThemeValue } from '@/components/themes/types';
import { valueToTheme } from '@/components/themes/utils';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { update, expose } = Fieldtype.use(emit, props);

const selectedTheme = ref<Theme | null>(
    props.value ? valueToTheme(props.value as ThemeValue) : null
);

const tab = ref<Tab>((() => {
    const theme = selectedTheme.value;
    if (!theme) return 'themes';
    return theme.id === 'custom' ? 'custom' : 'themes';
})());

function selected(theme: Theme) {
    selectedTheme.value = theme;

    if (theme.id === 'default') {
        update(null);
        return;
    }

    const normalizedColors = {
        ...theme.colors,
        ...Object.fromEntries(
            Object.entries(theme.darkColors || {}).map(([key, value]) => [`dark-${key}`, value])
        )
    };

    update({
        id: theme.id,
        name: theme.name,
        colors: normalizedColors
    });
}

defineExpose(expose);
</script>

<template>
    <Selector
        v-model:tab="tab"
        :model-value="selectedTheme"
        @update:model-value="selected"
    />
</template>
