import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import inject from '@rollup/plugin-inject';
import { visualizer } from 'rollup-plugin-visualizer';
import svgLoader from 'vite-svg-loader';
import path from 'path';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        base: './',
        plugins: [
            tailwindcss(),
            laravel({
                valetTls: env.VALET_TLS,
                input: ['resources/css/app.css', 'resources/js/index.js'],
                refresh: true,
                publicDirectory: 'resources/dist',
                hotFile: 'resources/dist/hot',
            }),
            vue(),
            svgLoader(),
            inject({ Vue: 'vue', include: 'resources/js/**' }),
        ],
        css: {
            devSourcemap: true,
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
                '@statamic/ui': path.resolve(__dirname, 'resources/js/components/ui/index.js'),
                '@statamic': path.resolve(__dirname, 'resources/js'),
                'statamic': path.resolve(__dirname, 'resources/js/exports.js'),
            },
        },
        optimizeDeps: { include: ['vue'] },
        build: { rollupOptions: { output: { plugins: [visualizer({ filename: 'bundle-stats.html' })] } } },
        test: { environment: 'jsdom', setupFiles: 'resources/js/tests/setup.js' },
    };
});
