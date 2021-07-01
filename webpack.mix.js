const mix = require('laravel-mix');
const webpack = require('webpack');
const tailwindcss = require('tailwindcss');
const src = 'resources';
const dest = 'resources/dist';

mix.setPublicPath('./resources/dist');

mix.sass(`${src}/sass/cp.scss`, `${dest}/css`).options({
    processCssUrls: false,
    postCss: [
        tailwindcss('./tailwind.config.js'),
        require('autoprefixer')
    ],
});

mix.js(`${src}/js/app.js`, `${dest}/js`);
mix.extract([
    '@popperjs/core',
    '@shopify/draggable',
    'alpinejs',
    'autosize',
    'axios',
    'codemirror',
    'cookies-js',
    'dmuploader',
    'jquery-ui',
    'jquery',
    'lazysizes',
    'luminous-lightbox',
    'marked-plaintext',
    'marked',
    'moment',
    'mousetrap',
    'speakingurl',
    'sweetalert',
    'underscore',
    'v-calendar',
    'vue-clickaway',
    'vue-js-modal',
    'vue-js-popover',
    'vue'
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
        // Some vendor files reference globals
        new webpack.ProvidePlugin({ $: "jquery", jQuery: "jquery" }),

        // Our files reference globals too
        new webpack.ProvidePlugin({ Vue: "vue" }),
        new webpack.ProvidePlugin({ Alpine: "Alpine" }),
        new webpack.ProvidePlugin({ _: "underscore" })
    ]
})
