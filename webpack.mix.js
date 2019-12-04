const mix = require('laravel-mix');
const webpack = require('webpack');
const tailwindcss = require('tailwindcss');
const src = 'resources';
const dest = 'resources/dist';

mix.setPublicPath('./resources/dist');

mix.sass(`${src}/sass/cp.scss`, `${dest}/css`).options({
    processCssUrls: false,
    postCss: [
        tailwindcss('./tailwind.js'),
        require('autoprefixer')
    ],
});

mix.js(`${src}/js/app.js`, `${dest}/js`);
mix.extract([
    'autosize',
    'axios',
    'codemirror',
    'cookies-js',
    'dmuploader',
    'jquery',
    'jquery-ui',
    'lazysizes',
    'luminous-lightbox',
    'marked',
    'marked-plaintext',
    'moment',
    'mousetrap',
    'selectize',
    'sweetalert',
    '@shopify/draggable',
    'speakingurl',
    'underscore',
    'v-calendar',
    'vue',
    'vue-clickaway',
    'vue-js-modal',
    'vue-js-popover',
]);

mix.copyDirectory(`${src}/img`, `${dest}/img`);
mix.copyDirectory(`${src}/svg`, `${dest}/svg`);
mix.copyDirectory(`${src}/audio`, `${dest}/audio`);
mix.copyDirectory(`${src}/fonts`, `${dest}/fonts`);

mix.sourceMaps();

mix.options({ extractVueStyles: true });

mix.webpackConfig({
    devtool: 'source-map',
    plugins: [
        // Vendor files (eg. twitter bootstrap) reference globals
        new webpack.ProvidePlugin({ $: "jquery", jQuery: "jquery" }),

        // Our files reference globals
        new webpack.ProvidePlugin({ Vue: "vue" }),
        new webpack.ProvidePlugin({ _: "underscore" })
    ]
})
