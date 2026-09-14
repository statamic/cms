import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: 'resources/dist-frontend/js',
        lib: {
            entry: 'resources/js/frontend/helpers.js',
            name: 'statamic',
            fileName: () => 'helpers.js',
            formats: ['umd'],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js'),
        },
    },
});
