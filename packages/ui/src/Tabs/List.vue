<script setup>
import { TabsIndicator, TabsList } from 'reka-ui';
import { hasComponent } from '@/composables/has-component.js';

const hasTabIndicatorComponent = hasComponent('TabsIndicator');
</script>

<template>
    <TabsList
        class="relative flex shrink-0 mx-2 lg:mx-0 space-x-2 lg:space-x-4 border-b border-gray-200 text-sm text-gray-500 
        dark:border-gray-700
        [.live-preview_&]:px-4 [.live-preview_&]:py-2 [.live-preview_&]:mb-3"
        data-ui-tabs-list
    >
        <TabsIndicator
            v-if="!hasTabIndicatorComponent"
            class="absolute bottom-0 left-0 h-px w-[var(--reka-tabs-indicator-size)] translate-x-[var(--reka-tabs-indicator-position)] translate-y-[1px] rounded-full bg-black transition duration-300 dark:bg-white"
            data-ui-tabs-indicator
        />
        <slot />
    </TabsList>
</template>
