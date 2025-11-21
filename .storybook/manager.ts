import { addons } from 'storybook/manager-api';
import { create } from 'storybook/theming';

const theme = create({
  base: 'light',
  brandTitle: 'Statamic UI Components',
  brandUrl: 'https://statamic.dev',
  brandImage: '/logo.png',
  appBg: 'linear-gradient(225deg, #e6f8ff, #f9e6ff, hsl(35deg 100% 95%))',
  fontBase: '"Lexend", sans-serif',
  fontCode: '"Source Code Pro", monospace'
});

addons.setConfig({
  theme,
});
