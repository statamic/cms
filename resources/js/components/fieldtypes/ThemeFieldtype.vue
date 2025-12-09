<script setup lang="ts">
import Fieldtype from '@/components/fieldtypes/fieldtype';
import { Button, Modal, Badge } from '@ui';
import { computed, ref } from 'vue';
import Selector from '../themes/Selector.vue';
import { Theme, ThemeValue } from '@/components/themes/types';
import { applyDefaultTheme, valueToTheme } from '@/components/themes/utils';
import { translate as __ } from '@/translations/translator';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { update, expose } = Fieldtype.use(emit, props);

const selecting = ref(false);

const selectedTheme = ref<Theme | null>(
    props.value ? valueToTheme(props.value as ThemeValue) : null
);

const themeName = computed(() => {
    if (!selectedTheme.value) {
        return __('Default');
    }

    return selectedTheme.value.name ?? selectedTheme.value.id ?? __('Custom');
});

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

function resetToDefault() {
    selectedTheme.value = null;
    update(null);
    applyDefaultTheme();
}

defineExpose(expose);
</script>

<template>
    <div class="mt-2 flex flex-wrap items-center gap-2">
        <Button as="div" variant="filled" size="sm" :text="themeName" />
        <Button size="sm" :text="__('Select...')" @click="selecting = true" />
        <Button v-if="value" size="sm" :text="__('Reset to default')" @click="resetToDefault" />
    </div>

    <Modal v-model:open="selecting" :title="__('Theme')">
        <Selector
            :model-value="selectedTheme"
            @update:model-value="selected"
        />
    </Modal>
</template>
