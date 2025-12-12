<script setup lang="ts">
import { Tabs, TabList, TabTrigger, TabContent } from '@ui';
import { ref, watch } from 'vue';
import Themes from './Themes.vue';
import Custom from './Custom.vue';
import { Theme, PredefinedTheme } from './types';
import { applyTheme, applyDefaultTheme } from '.';
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

const activeTab = ref<Tab>(props.tab);

watch(activeTab, (newTab) => {
    emit('update:tab', newTab);

    if (newTab === 'themes' && customWasShared.value) {
        themes.value?.refresh();
        customWasShared.value = false;
    }
});

function themeSelected(theme: PredefinedTheme) {
    emit('update:modelValue', theme);

    if (theme.id === 'custom') activeTab.value = 'custom';
}

const customWasShared = ref(false);

function customUpdated(theme: Theme) {
    emit('update:modelValue', theme);
}

watch(
    () => props.modelValue,
    (newValue) => newValue ? applyTheme(newValue) : applyDefaultTheme(),
    { immediate: true }
)
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
                    :model-value="modelValue"
                    @update:model-value="customUpdated"
                    @shared="customWasShared = true"
                />
            </div>
        </TabContent>
    </Tabs>
</template>
