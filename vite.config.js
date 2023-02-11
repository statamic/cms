import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue2';
import inject from '@rollup/plugin-inject';
import svgLoader from './vite-svg-loader';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: '/vendor/statamic/cp/build',
        plugins: [
            laravel({
                valetTls: env.VALET_TLS,
                input: [
                    'resources/css/tailwind.css',
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
                _: 'underscore',
                include: 'resources/js/**'
            })
        ],
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm.js',
            }
        }
    }
});
