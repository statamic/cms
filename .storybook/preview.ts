import type { Preview } from "@storybook/vue3-vite";
import { setup } from '@storybook/vue3';
import { create } from 'storybook/theming';
import './storybook.css';
import './theme.css';
import { __, $date, Link, mockComponents } from './mocks';

const docsTheme = create({
  base: 'light',
  fontBase: '"Lexend", sans-serif',
  fontCode: '"Source Code Pro", monospace',
});

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
        layout: 'centered',

        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },

        docs: {
            theme: docsTheme,
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

        options: {
            storySort: {
                order: ['Getting Started', 'Installation', '*', 'Components'],
            },
        },
    },
};

export default preview;
