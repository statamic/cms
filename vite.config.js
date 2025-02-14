import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inject from '@rollup/plugin-inject';
import svgLoader from 'vite-svg-loader';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: './',
        plugins: [
            laravel({
                valetTls: env.VALET_TLS,
                input: ['resources/css/tailwind.css', 'resources/js/index.js'],
                refresh: true,
                publicDirectory: 'resources/dist',
                hotFile: 'resources/dist/hot',
            }),
            vue(),
            svgLoader(),
            inject({
                Vue: 'vue',
                include: 'resources/js/**',
            }),
        ],
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@': path.resolve(__dirname, 'resources/js'),
            },
        },
        optimizeDeps: {
            include: ['vue'],
        },
        test: {
            environment: 'jsdom',
            setupFiles: 'resources/js/tests/setup.js',
        },
    };
});
