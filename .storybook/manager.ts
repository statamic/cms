import { addons } from 'storybook/manager-api';
import { create } from 'storybook/theming';
import './manager.css';

const theme = create({
  base: 'light',
  brandTitle: 'Statamic UI Components',
  brandUrl: 'https://statamic.dev',
  brandImage: '/logo.png',
  fontBase: '"Lexend", sans-serif',
  fontCode: '"Source Code Pro", monospace'
});

addons.setConfig({
  theme,
});
