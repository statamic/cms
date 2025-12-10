<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Preview from './Preview.vue';
import type { PredefinedTheme, Theme } from './types';
import { applyTheme } from './utils';
import { nativeThemes } from './themes';
import { Description } from '@ui';
import { cp_url } from '@/bootstrap/globals';

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

const themes = ref<PredefinedTheme[]>(nativeThemes);

onMounted(() => load());

async function load() {
    try {
        const { data: marketplaceThemes } = await axios.get(cp_url('themes'));
        themes.value = [...nativeThemes, ...marketplaceThemes];
    } catch (error) {
        console.error('Failed to load marketplace themes:', error);
    }
}

function isActive(theme: PredefinedTheme): boolean {
    const activeThemeId = props.modelValue?.id || 'default';
    return activeThemeId === theme.id;
}

function refresh() {
    axios.get(cp_url('themes/refresh')).then(() => load());
}

defineExpose({
    refresh
})
</script>

<template>
    <div class="grid grid-cols-4 gap-6">
        <div
            v-for="theme in themes"
            :key="theme.id"
            :class="{ '[&_[data-preview]]:ring-2 [&_[data-preview]]:ring-blue-400 [&_[data-preview]]:rounded': isActive(theme) }"
            @click="selectTheme(theme)"
        >
            <Preview :theme="theme" />
            <div class="text-center pt-2">
                <Description :text="`${theme.name} <span class='opacity-70 text-2xs'>by</span> ${theme.author}`" />
            </div>
        </div>
    </div>
</template>
