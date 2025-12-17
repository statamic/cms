<script setup lang="ts">
import { TabContent, TabList, Tabs, TabTrigger } from '@ui';
import { onUnmounted, ref, watch } from 'vue';
import Themes from './Themes.vue';
import Custom from './Custom.vue';
import { Theme } from './types';
import { applyDefaultTheme, applyTheme, removeDefaults, toSelectionValue } from '.';
import { translate as __ } from '@/translations/translator';

export type Tab = 'themes' | 'custom';

const props = defineProps<{
    modelValue?: Theme;
    tab: Tab
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
    (e: 'update:tab', tab: Tab): void;
}>();

const themes = ref<InstanceType<typeof Themes>>();
const origin = ref<Theme | null>(null);

const activeTab = ref<Tab>(props.tab);

watch(activeTab, (newTab) => {
    emit('update:tab', newTab);

    if (newTab === 'themes' && customWasShared.value) {
        themes.value?.refresh().then(() => selectNewlyPublishedTheme());
        customWasShared.value = false;
    }
});

function themeSelected(theme: Theme | null) {
    emit('update:modelValue', toSelectionValue(theme));

    if (theme?.id === 'custom') {
        activeTab.value = 'custom';
    } else {
        origin.value = theme;
    }
}

const customWasShared = ref(false);

function customUpdated(theme: Theme) {
    emit('update:modelValue', toSelectionValue(theme));
}

watch(
    () => props.modelValue,
    (newValue) => newValue ? applyTheme(newValue) : applyDefaultTheme(),
)

const originalTheme = props.modelValue;
onUnmounted(() => applyTheme(originalTheme));

function selectNewlyPublishedTheme() {
    if (!props.modelValue || props.modelValue.id !== 'custom') return;
    if (!themes.value?.marketplaceThemes) return;

    const currentColors = props.modelValue.colors || {};
    const currentDarkColors = props.modelValue.darkColors || {};
    const recentThemes = themes.value.marketplaceThemes.slice(-5).reverse();

    for (let theme of recentThemes) {
        theme = removeDefaults(theme);
        const colorsMatch = objectsMatch(currentColors, theme.colors || {});
        const darkColorsMatch = objectsMatch(currentDarkColors, theme.darkColors || {});
        if (colorsMatch && darkColorsMatch) {
            themeSelected(theme);
            return;
        }
    }
}

function objectsMatch(a: object, b: object): boolean {
    const keysA = Object.keys(a);
    const keysB = Object.keys(b);

    if (keysA.length !== keysB.length) return false;

    return keysA.every(key => a[key] === b[key]);
}
</script>

<template>
    <Tabs v-model="activeTab" :unmount-on-hide="false">
        <TabList>
            <TabTrigger name="themes" :text="__('Themes')" />
            <TabTrigger name="custom" :text="__('Custom')" />
        </TabList>
        <TabContent name="themes">
            <div class="py-4">
                <Themes
                    ref="themes"
                    :model-value="modelValue"
                    @update:model-value="themeSelected"
                />
            </div>
        </TabContent>
        <TabContent name="custom">
            <div class="py-4">
                <Custom
                    :origin="origin"
                    :model-value="modelValue"
                    @update:model-value="customUpdated"
                    @shared="customWasShared = true"
                />
            </div>
        </TabContent>
    </Tabs>
</template>
