<script setup lang="ts">
import { ref } from 'vue';
import Preview from './Preview.vue';
import type { PredefinedTheme, Theme } from './types';
import { applyTheme } from './utils';
import { nativeThemes, marketplaceThemes } from './themes';
import { Description } from '@ui';

const emit = defineEmits<{
    (e: 'update:modelValue', theme: PredefinedTheme): void;
}>();

const props = defineProps<{
    modelValue?: Theme;
}>();

const selectTheme = (theme: PredefinedTheme) => {
    applyTheme(theme);
    emit('update:modelValue', theme);
};

const themes = ref<PredefinedTheme[]>([
    ...nativeThemes,
    ...marketplaceThemes
]);

function isActive(theme: PredefinedTheme): boolean {
    const activeThemeId = props.modelValue?.id || 'default';
    return activeThemeId === theme.id;
}
</script>

<template>
    <div class="grid grid-cols-3 gap-6">
        <div
            v-for="theme in themes"
            :key="theme.id"
            :class="{ '[&_[data-preview]]:ring-2 [&_[data-preview]]:ring-blue-400 [&_[data-preview]]:rounded': isActive(theme) }"
            @click="selectTheme(theme)"
        >
            <Preview :theme="theme" />
            <div class="text-center pt-2">
                <Description :text="theme.name" />
            </div>
        </div>
    </div>
</template>
