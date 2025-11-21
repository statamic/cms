import type { Preview } from "@storybook/vue3-vite";
import { setup } from '@storybook/vue3';
import { create } from 'storybook/theming';
import { router } from '@inertiajs/vue3';
import './storybook.css';
import './theme.css';
import { __, $date, mockComponents } from './mocks';

// Intercept Inertia navigation and show alert instead.
router.on('before', (event) => {
  alert(`Navigating to: ${event.detail.visit.url}`);
  return false;
});

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
            disable: true
        },

        options: {
            storySort: {
                order: ['Getting Started', 'Installation', '*', 'Components'],
            },
        },
    },
};

export default preview;
