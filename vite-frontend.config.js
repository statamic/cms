import { defineConfig } from 'vite';

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
});
