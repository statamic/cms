import type { StorybookConfig } from '@storybook/vue3-vite';

const config: StorybookConfig = {
    stories: [
        '../resources/js/stories/**/*.mdx',
        '../resources/js/stories/**/*.stories.@(js|jsx|mjs|ts|tsx)'
    ],
    addons: [
        '@storybook/addon-docs',
        '@storybook/addon-a11y'
    ],
    staticDirs: ['./public'],
    framework: {
        name: '@storybook/vue3-vite',
        options: {
            docgen: 'vue-component-meta'
        }
    },
};

export default config;
