import type { Preview } from "@storybook/vue3-vite";
import { setup } from '@storybook/vue3';
import './storybook.css';
import './theme.css';
import { __, $date, Link, mockComponents } from './mocks';

// Setup global mocks
setup((app) => {
  // Mock global functions
  app.config.globalProperties.__ = __;
  app.config.globalProperties.$date = $date;

  // Register mock components
  app.component('Link', Link);

  // Register custom element mocks
  Object.entries(mockComponents).forEach(([name, component]) => {
    app.component(name, component);
  });
});

const preview: Preview = {
    parameters: {
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },

        a11y: {
            test: "todo",
        },

        backgrounds: {
            default: 'light',
            values: [
                { name: 'light', value: '#ffffff' },
                { name: 'gray', value: '#f7f8fa' },
                { name: 'dark', value: '#1a1a1a' },
            ],
        },
    },
};

export default preview;
