<script setup lang="ts">
import type { Theme } from './types';
import { computed } from 'vue';
import { getDefaultTheme, getCssVariables } from '.';
import { nanoid as uniqid } from 'nanoid';

const props = withDefaults(defineProps<{
    theme: Theme;
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
        <div data-preview :id="uniqueId" class="w-full aspect-video border border-gray-800 dark:border-gray-700 shadow-md rounded-lg overflow-hidden text-3xs">
            <div class="h-full flex flex-col bg-global-header-bg">
                <header class="bg-global-header-bg px-2 py-1 flex items-center justify-between flex-shrink-0">
                    <div class="flex items-center gap-1.5">
                        <div class="flex flex-col gap-[1px]">
                            <div class="bg-white/85 h-[1px] w-1.5"></div>
                            <div class="bg-white/85 h-[1px] w-1.5"></div>
                            <div class="bg-white/85 h-[1px] w-1.5"></div>
                        </div>
                        <div class="size-2 shape-squircle bg-volt rounded-sm"></div>
                        <div class="p-crumb h-[3px] w-6 rounded bg-white/85"></div>
                        <div class="p-crumb h-[3px] w-6 rounded bg-white/85"></div>
                        <div class="p-crumb h-[3px] w-6 rounded bg-white/85"></div>
                    </div>
                    <div>
                        <div class="size-2 shape-squircle bg-white rounded-full"></div>
                    </div>
                </header>
                <div class="flex-1 bg-[var(--theme-color-body-bg)] grid grid-cols-[1fr_5fr] gap-3 p-2 rounded-t-md overflow-hidden min-h-0">
                    <div class="p-nav flex flex-col gap-1 pt-2">
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px] w-1/3"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px] w-1/3 mt-2"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px] w-1/3 mt-2"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                        <div class="bg-[var(--theme-color-gray-700)] opacity-50 h-[2px]"></div>
                    </div>
                    <div class="bg-[var(--theme-color-content-bg)] rounded p-2">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1">
                                <div class="bg-[var(--preview-color-text)] h-1 w-1" />
                                <div class="bg-[var(--preview-color-text)] h-1 w-10" />
                            </div>
                            <div class="bg-[var(--theme-color-primary)] w-8 h-3 rounded-sm flex items-center justify-center">
                                <div class="bg-white/85 h-1 w-2/3 rounded-sm"></div>
                            </div>
                        </div>
                        <div class="mt-2 bg-[var(--preview-color-card-panel-border)] p-1 rounded-md">
                            <div class=" bg-[var(--preview-color-card-panel)] p-2 rounded-sm shadow-sm flex items-center justify-between">
                                <div class="flex flex-col gap-1">
                                    <div class="bg-[var(--preview-color-text)] h-1 w-4" />
                                    <div class="bg-[var(--preview-color-text)] h-1 w-12" />
                                </div>

                                <div class="bg-[var(--theme-color-switch-bg)] w-5 rounded-full">
                                    <div class="p-[3px] size-3 flex">
                                        <div class="rounded-full h-full w-full bg-white"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
