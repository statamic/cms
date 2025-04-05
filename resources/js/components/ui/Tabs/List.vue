<script setup>
import { TabsIndicator, TabsList } from 'reka-ui';
import { hasComponent } from '@statamic/composables/has-component.js';

const hasTabIndicatorComponent = hasComponent('TabsIndicator');
</script>

<template>
    <TabsList
        class="relative flex shrink-0 space-x-4 border-b border-gray-200 text-sm text-gray-400 dark:border-gray-700 dark:text-gray-500"
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
