import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import inject from '@rollup/plugin-inject';
import svgLoader from './vite-svg-loader';

export default defineConfig({
    base: '/vendor/statamic/cp/build',
    plugins: [
        laravel({
            valetTls: 'statamic3.test',
            input: [
                'resources/sass/cp.scss',
                'resources/js/app.js'
            ],
            refresh: true,
            publicDirectory: 'resources/dist',
            hotFile: 'resources/dist/hot',
        }),
        vue(),
        svgLoader(),
        inject({
            Vue: 'vue',
            jQuery: 'jquery',
            _: 'underscore',
            include: 'resources/js/**'
        })
    ],
    resolve: {
        alias: {
            vue: 'vue/dist/vue.esm.js',
        }
    }
});
