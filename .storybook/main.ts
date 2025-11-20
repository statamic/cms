import type { StorybookConfig } from '@storybook/vue3-vite';
import { mergeConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const config: StorybookConfig = {
  stories: [
    "../resources/js/stories/**/*.mdx",
    "../resources/js/stories/**/*.stories.@(js|jsx|mjs|ts|tsx)"
  ],
  addons: [
    "@storybook/addon-docs",
    "@storybook/addon-a11y"
  ],
  staticDirs: ['./public'],
  framework: {
    name: "@storybook/vue3-vite",
    options: {}
  },
  async viteFinal(config) {
    config.plugins = config.plugins || [];
    config.plugins.push(tailwindcss());

    return mergeConfig(config, {
      resolve: {
        alias: {
          '@statamic/ui': path.resolve(__dirname, '../packages/ui/src'),
          '@/composables/has-component.js': path.resolve(__dirname, './composables-mock.js'),
          '@/composables/has-component': path.resolve(__dirname, './composables-mock.js'),
          '@': path.resolve(__dirname, '../resources/js'),
          '@inertiajs/vue3': path.resolve(__dirname, './inertia-mock.js'),
        },
      },
      optimizeDeps: {
        include: ['reka-ui', 'cva', 'tailwind-merge', '@storybook/blocks'],
      },
    });
  },
};
export default config;
