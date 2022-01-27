const mix = require('laravel-mix');
const src = 'resources';
const dest = 'resources/dist-frontend';

mix.setPublicPath('./resources/dist-frontend');

mix.js(`${src}/js/frontend/helpers.js`, `${dest}/js`)

mix.sourceMaps();
