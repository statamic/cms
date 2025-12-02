import type { Preview } from '@storybook/vue3-vite';
import { setup } from '@storybook/vue3';
import { create as createTheme } from 'storybook/theming';
import { router } from '@inertiajs/vue3';
import { action } from 'storybook/actions';
import './storybook.css';
import './theme.css';
import { translate } from '@/translations/translator';
import registerUiComponents from '@/bootstrap/ui';
import DateFormatter from '@/components/DateFormatter';
import cleanCodeSnippet from './clean-code-snippet';

// Intercept Inertia navigation and log to Actions tab.
router.on('before', (event) => {
  action('inertia navigate')(event.detail.visit.url);
  return false;
});

setup(async (app) => {
  app.config.globalProperties.__ = translate;
  app.config.globalProperties.$date = new DateFormatter;
  await registerUiComponents(app);
});

const preview: Preview = {
    parameters: {
        controls: {
            matchers: {
                color: /(background|color)$/i,
                date: /Date$/i,
            },
        },

        docs: {
            theme: createTheme({
                base: 'light',
                fontBase: '"Lexend", sans-serif',
                fontCode: '"Source Code Pro", monospace',
            }),
            source: {
                transform: (code: string) => cleanCodeSnippet(code),
            },
        },

        a11y: {
            test: "todo",
        },

        backgrounds: {
            disabled: true
        },

        options: {
            storySort: {
                order: ['Getting Started', 'Installation', '*', 'Components'],
            },
        },
    },
};

export default preview;
