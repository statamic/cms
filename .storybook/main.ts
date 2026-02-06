import type { StorybookConfig } from '@storybook/vue3-vite';
import { resolve } from 'path';

const config: StorybookConfig = {
    stories: [
        '../resources/js/stories/**/*.mdx',
        '../resources/js/stories/**/*.stories.@(js|jsx|mjs|ts|tsx)'
    ],
    addons: [
        '@storybook/addon-docs',
        '@storybook/addon-a11y',
        '@storybook/addon-vitest'
    ],
    staticDirs: ['./public'],
    framework: {
        name: '@storybook/vue3-vite',
        options: {
            docgen: 'vue-component-meta'
        }
    },
    async viteFinal(config) {
        if (config.resolve) {
            config.resolve.alias = {
                ...config.resolve.alias,
                '@api': resolve(process.cwd(), 'resources/js/api.js'),
            };
        }
        return config;
    },
};

export default config;
