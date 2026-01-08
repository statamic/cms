import type {Preview} from '@storybook/vue3-vite';
import {setup} from '@storybook/vue3';
import {create as createTheme} from 'storybook/theming';
import {router} from '@inertiajs/vue3';
import {action} from 'storybook/actions';
import './storybook.css';
import './theme.css';
import {translate} from '@/translations/translator';
import registerUiComponents from '@/bootstrap/ui';
import DateFormatter from '@/components/DateFormatter';
import cleanCodeSnippet from './clean-code-snippet';
import PortalVue from 'portal-vue';
import FullscreenHeader from '@/components/publish/FullscreenHeader.vue';
import Portal from '@/components/portals/Portal.vue';
import PortalTargets from '@/components/portals/PortalTargets.vue';
import { portals, stacks } from '@api';

// Intercept Inertia navigation and log to Actions tab.
router.on('before', (event) => {
  action('inertia navigate')(event.detail.visit.url);
  return false;
});

setup(async (app) => {
  window.__ = translate;

  window.Statamic = {
      $config: {
          get(key) {
              const config = {
                  linkToDocs: true,
                  paginationSize: 50,
                  paginationSizeOptions: [10, 25, 50, 100, 500],
              };

              return config[key] ?? null;
          }
      },
      $commandPalette: {
          add(command) {
              //
          }
      },
      $progress: {
          loading(name, loading) {
              //
          }
      }
  };

  app.config.globalProperties.__ = translate;
  app.config.globalProperties.$date = new DateFormatter;
  app.config.globalProperties.cp_url = (url) => url;
  app.config.globalProperties.$portals = portals;
  app.config.globalProperties.$stacks = stacks;

  app.use(PortalVue, { portalName: 'v-portal' });

  app.component('portal', Portal);
  app.component('PortalTargets', PortalTargets);
  app.component('publish-field-fullscreen-header', FullscreenHeader);

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
                    'Overview',
                    'Components',
                    '*'
                ],
                method: 'alphabetical',
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

            return {
                components: { PortalTargets },
                template: '<div><story /><PortalTargets /></div>',
            };
        },
    ],
};

export default preview;
