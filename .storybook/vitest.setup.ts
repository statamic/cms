import * as a11yAddonAnnotations from "@storybook/addon-a11y/preview";
import { setProjectAnnotations } from '@storybook/vue3-vite';
import * as projectAnnotations from './preview';

// Suppress known harmless warnings during Storybook tests.
const shouldSuppressMessage = (message: unknown): boolean => {
    if (typeof message !== 'string') return false;

    // Vue 3.4+ issue with no upstream fix. Occurs when Storybook preprocesses
    // stories with inline templates in Node.js.
    // https://github.com/orgs/vuejs/discussions/10065
    if (message.includes('decodeEntities option is passed but will be ignored')) return true;

    return false;
};

const originalWarn = console.warn;
console.warn = (...args: unknown[]) => {
    if (shouldSuppressMessage(args[0])) return;
    originalWarn.apply(console, args);
};

// This is an important step to apply the right configuration when testing your stories.
// More info at: https://storybook.js.org/docs/api/portable-stories/portable-stories-vitest#setprojectannotations
setProjectAnnotations([a11yAddonAnnotations, projectAnnotations]);
