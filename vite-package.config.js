import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
export default defineConfig({
    plugins: [
        vue()
    ],
    build: {
        lib: {
            entry: {
                'index': path.resolve(__dirname, 'resources/js/package/index.js'),
                'ui': path.resolve(__dirname, 'resources/js/package/ui.js'),
                'bard': path.resolve(__dirname, 'resources/js/package/bard.js'),
                'save-pipeline': path.resolve(__dirname, 'resources/js/package/save-pipeline.js')
            },
            formats: ['es']
        },
        outDir: 'resources/dist-package',
        rollupOptions: {
            external: ['vue'],
            output: {
                globals: {
                    vue: 'Vue'
                },
                preserveModules: true,
                preserveModulesRoot: 'resources/js/package'
            }
        }
    },
    resolve: {
        alias: {
            '@statamic/cms': path.resolve(__dirname, 'resources/js/package'),
            '@': path.resolve(__dirname, 'resources/js')
        }
    }
});
