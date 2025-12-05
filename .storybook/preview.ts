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
  window.__ = translate;
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

        backgrounds: {
            disable: true
        },

        options: {
            storySort: {
                order: [
                    // 'Getting Started',
                    // 'Installation',
                    '*',
                    'Components'
                ],
            },
        },
    },

    globalTypes: {
        theme: {
            description: 'Theme selector',
            defaultValue: 'light',
            toolbar: {
                title: 'Theme',
                icon: 'circlehollow',
                items: [
                    { value: 'light', icon: 'sun', title: 'Light' },
                    { value: 'dark', icon: 'moon', title: 'Dark' },
                ],
                dynamicTitle: true,
            },
        },
    },

    decorators: [
        (story, context) => {
            const theme = context.globals.theme || 'light';

            if (typeof document !== 'undefined') {
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            }

            return story();
        },
    ],
};

export default preview;
