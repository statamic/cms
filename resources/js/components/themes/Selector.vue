<script setup lang="ts">
import { Tabs, TabList, TabTrigger, TabContent } from '@ui';
import { ref, watch } from 'vue';
import Themes from './Themes.vue';
import Custom from './Custom.vue';
import { Theme, PredefinedTheme } from './types';
import { applyTheme, applyDefaultTheme } from './utils';
import { translate as __ } from '@/translations/translator';

const props = defineProps<{
    modelValue?: Theme;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
}>();

const tab = ref<'themes' | 'custom'>('themes');

function themeSelected(theme: PredefinedTheme) {
    emit('update:modelValue', theme);
}

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
    <Tabs v-model="tab">
        <TabList>
            <TabTrigger name="themes" :text="__('Themes')" />
            <TabTrigger name="custom" :text="__('Custom')" />
        </TabList>
        <TabContent name="themes">
            <div class="py-4">
                <Themes
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
                />
            </div>
        </TabContent>
    </Tabs>
</template>
