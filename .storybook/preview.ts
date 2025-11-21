import type { Preview } from "@storybook/vue3-vite";
import { setup } from '@storybook/vue3';
import { create } from 'storybook/theming';
import { router } from '@inertiajs/vue3';
import './storybook.css';
import './theme.css';
import { translate } from '@/translations/translator';
import registerUiComponents from '@/bootstrap/ui';
import DateFormatter from '@/components/DateFormatter';

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
setup(async (app) => {
  app.config.globalProperties.__ = translate;
  app.config.globalProperties.$date = new DateFormatter;
  await registerUiComponents(app);
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
