import { addons } from 'storybook/manager-api';
import { create } from 'storybook/theming';
import './manager.css';

const theme = create({
  base: 'light',
  brandTitle: 'Statamic UI Components',
  brandUrl: 'https://statamic.dev',
  brandImage: '/logo.svg',
  fontBase: '"Lexend", sans-serif',
  fontCode: '"Source Code Pro", monospace',

   appBorderColor: 'hsl(287deg 80% 90%)',

    // Theme selector. This is overlaid on top.
    barSelectedColor: '#ff0000',
});

addons.setConfig({
  theme,
});
