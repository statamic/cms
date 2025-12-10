<script setup lang="ts">
import type { PredefinedTheme } from './types';
import { computed } from 'vue';
import { getDefaultTheme, getCssVariables } from './utils';
import uniqid from 'uniqid';

const props = withDefaults(defineProps<{
    theme: PredefinedTheme;
    appearance?: 'auto' | 'light' | 'dark';
}>(), {
    appearance: 'auto'
});

const uniqueId = `preview-${uniqid()}`;

const appearanceClass = computed(() => props.appearance === 'auto' ? '' : props.appearance);

const mergedColors = computed(() => {
    const defaults = getDefaultTheme();
    return { ...defaults.colors, ...props.theme.colors };
});

const mergedDarkColors = computed(() => {
    const defaults = getDefaultTheme();
    return { ...defaults.darkColors, ...props.theme.darkColors };
});

const dynamicStyles = computed(() => {
    let { light, dark } = getCssVariables(mergedColors.value, mergedDarkColors.value);

    light += `
        --preview-color-text: var(--theme-color-gray-300);
        --preview-color-card-panel-border: var(--theme-color-gray-150);
        --preview-color-card-panel: white;
    `;

    return `
        #${uniqueId} {
            ${light}
        }

        .dark #${uniqueId} {
            ${dark}
            --preview-color-text: var(--theme-color-gray-500);
            --preview-color-card-panel-border: var(--theme-color-gray-950);
            --preview-color-card-panel: var(--theme-color-gray-850);
        }

        .light #${uniqueId} {
            ${light}
        }
    `;
});
</script>

<template>
    <div :class="appearanceClass">
        <component is="style">{{ dynamicStyles }}</component>
        <div data-preview :id="uniqueId" class="w-full aspect-video border border-gray-800 shadow-md rounded-lg overflow-hidden text-[clamp(0.625rem,1.5vw,0.875rem)]">
            <div class="h-full flex flex-col bg-global-header-bg">
                <header class="bg-global-header-bg px-2 py-1.5 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-2">
                        <div class="flex flex-col gap-0.5">
                            <div class="bg-white/85 h-0.5 w-2"></div>
                            <div class="bg-white/85 h-0.5 w-2"></div>
                            <div class="bg-white/85 h-0.5 w-2"></div>
                        </div>
                        <div class="size-2.5 shape-squircle bg-volt rounded-sm"></div>
                        <div class="p-crumb h-1.5 w-10 rounded bg-white/85"></div>
                        <div class="p-crumb h-1.5 w-10 rounded bg-white/85"></div>
                        <div class="p-crumb h-1.5 w-10 rounded bg-white/85"></div>
                    </div>
                    <div>
                        <div class="size-2.5 shape-squircle bg-white rounded-full"></div>
                    </div>
                </header>
                <div class="flex-1 bg-[var(--theme-color-body-bg)] grid grid-cols-[1.5fr_4.5fr] gap-3 p-3 rounded-t-md overflow-hidden min-h-0">
                    <div class="p-nav flex flex-col gap-1.5 pt-3 pr-3">
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1 w-1/3"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1 w-1/3 mt-3"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1 w-1/3 mt-3"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-1"></div>
                    </div>
                    <div class="bg-[var(--theme-color-content-bg)] rounded p-3 flex flex-col gap-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <div class="bg-[var(--preview-color-text)] h-1.5 w-1.5 rounded-full"></div>
                                <div class="bg-[var(--preview-color-text)] h-1.5 w-16"></div>
                            </div>
                            <div class="bg-[var(--theme-color-primary)] w-10 h-4 rounded-sm flex items-center justify-center">
                                <div class="bg-white/85 h-1.5 w-2/3 rounded-sm"></div>
                            </div>
                        </div>
                        <div class="bg-[var(--preview-color-card-panel-border)] p-1.5 rounded-md">
                            <div class="bg-[var(--preview-color-card-panel)] p-3 rounded-sm shadow-sm flex items-center justify-between">
                                <div class="flex flex-col gap-1.5">
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-8"></div>
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-20"></div>
                                </div>
                                <div class="bg-[var(--theme-color-switch-bg)] w-6 rounded-full">
                                    <div class="p-0.5 size-4 flex">
                                        <div class="rounded-full h-full w-full bg-white"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-1">
                            <div class="bg-[var(--preview-color-card-panel-border)] p-2 rounded-md">
                                <div class="bg-[var(--preview-color-card-panel)] p-2 rounded-sm">
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-3/4 mb-1.5"></div>
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-full opacity-60"></div>
                                </div>
                            </div>
                            <div class="bg-[var(--preview-color-card-panel-border)] p-2 rounded-md">
                                <div class="bg-[var(--preview-color-card-panel)] p-2 rounded-sm">
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-2/3 mb-1.5"></div>
                                    <div class="bg-[var(--preview-color-text)] h-1.5 w-full opacity-60"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
