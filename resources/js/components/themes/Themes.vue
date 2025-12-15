<script setup lang="ts">
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Preview from './Preview.vue';
import type { Theme } from './types';
import { applyTheme, defaultTheme } from '.';
import { Button, Description, Input } from '@ui';
import { cp_url } from '@/bootstrap/globals';
import fuzzysort from 'fuzzysort';

const emit = defineEmits<{
    (e: 'update:modelValue', theme: Theme): void;
}>();

const props = defineProps<{
    modelValue?: Theme;
}>();

const selectTheme = (theme: Theme) => {
    applyTheme(theme);
    emit('update:modelValue', theme);
};

const localThemes = computed<Theme[]>(() => {
    const themes = [defaultTheme];

    if (props.modelValue?.id === 'custom') {
        themes.push(props.modelValue);
    } else {
        themes.push({
            ...(props.modelValue ?? defaultTheme),
            id: 'custom',
            name: 'Custom',
            author: null,
        });
    }

    return themes;
});

const marketplaceThemes = ref<Theme[]>([]);
const themes = computed<Theme[]>(() => [...localThemes.value, ...marketplaceThemes.value]);
const busy = ref<boolean>(true);
const search = ref<string>('');

const results = computed(() => {
    return search.value
        ? fuzzysort
            .go(search.value, themes.value, { keys: ['name', 'author'] })
            .map(result => result.obj)
        : themes.value;
});

onMounted(() => load());

function load() {
    return axios.get(cp_url('themes'))
        .then(({ data }) => marketplaceThemes.value = data)
        .catch(error => console.error('Failed to load marketplace themes:', error))
        .finally(() => busy.value = false);
}

function isActive(theme: Theme): boolean {
    const activeThemeId = props.modelValue?.id || 'default';
    return activeThemeId === theme.id;
}

function refresh() {
    busy.value = true;
    return axios.get(cp_url('themes/refresh')).then(() => load());
}

function themeDescription(theme: Theme): string {
    let description = theme.name;
    if (theme.author) description += ` <span class='opacity-70 text-2xs'>by</span> ${theme.author}`;
    return description;
}

defineExpose({
    refresh,
    marketplaceThemes
})
</script>

<template>
    <div class="@container/themes">
        <div class="mb-6 flex items-center gap-4">
            <Input
                size="sm"
                v-model="search"
                :placeholder="__('Search...')"
                clearable
                @keydown.esc="search = ''"
            />
            <Button
                size="sm"
                variant="filled"
                @click="refresh"
                :disabled="busy"
                :icon="busy ? 'loading' : 'live-preview'"
            />
        </div>

        <div class="grid grid-cols-2 @lg/themes:grid-cols-3 @2xl/themes:grid-cols-4 gap-4">
            <button
                v-for="theme in results"
                :key="theme.id"
                class="p-1 rounded-xl hover:bg-blue-100 dark:hover:bg-blue-900"
                :class="{ 'bg-blue-400! dark:bg-blue-500!': isActive(theme) }"
                @click="selectTheme(theme)"
            >
                <Preview :theme="theme" />
                <div class="text-center pb-1 py-1.5">
                    <Description :text="themeDescription(theme)" :class="{ 'text-white!': isActive(theme) }" />
                </div>
            </button>
        </div>
    </div>
</template>
