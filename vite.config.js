import { defineConfig, loadEnv } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';
import { visualizer } from 'rollup-plugin-visualizer';
import svgLoader from 'vite-svg-loader';
import path from 'path';
import { playwright } from '@vitest/browser-playwright';
import tsconfigPaths from 'vite-tsconfig-paths';

export default defineConfig(({ mode, command }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const isRunningBuild = command === 'build';
    const isProdBuild = isRunningBuild && mode === 'production';
    const isProdDevBuild = isRunningBuild && mode === 'development';

    return {
        base: './',
        server: {
            watch: {
                ignored: ['**/tests/**', '**/vendor/**']
            }
        },
        plugins: [
            tsconfigPaths(),
            tailwindcss(),
            laravel({
                valetTls: env.VALET_TLS,
                input: ['resources/css/app.css', 'resources/js/index.js'],
                refresh: true,
                publicDirectory: isProdDevBuild ? 'resources/dist-dev' : 'resources/dist',
                hotFile: 'resources/dist/hot',
            }),
            vue(),
            svgLoader(),
        ],
        css: {
            devSourcemap: true,
        },
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm-bundler.js',
            },
        },
        build: {
            rollupOptions: {
                output: {
                    plugins: [visualizer({ filename: 'bundle-stats.html' })]
                },
            },
            minify: isProdBuild
        },
        test: {
            projects: [
                {
                    extends: true,
                    test: {
                        name: 'unit',
                        environment: 'jsdom',
                        setupFiles: 'resources/js/tests/setup.js',
                        include: ['resources/js/tests/**/*.test.js'],
                        exclude: ['resources/js/tests/browser/**'],
                    },
                },
                {
                    extends: true,
                    test: {
                        name: 'browser',
                        setupFiles: 'resources/js/tests/setup.js',
                        include: ['resources/js/tests/browser/**/*.test.js'],
                        browser: {
                            enabled: true,
                            provider: playwright(),
                            instances: [{ browser: 'chromium' }],
                        },
                    },
                },
            ],
        },
        define: {
            __VUE_PROD_DEVTOOLS__: isProdDevBuild,
            ...(isRunningBuild && { 'process.env.NODE_ENV': isProdDevBuild ? '"development"' : '"production"' }),
        }
    };
});
