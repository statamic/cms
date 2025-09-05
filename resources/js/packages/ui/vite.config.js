import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

export default defineConfig({
    plugins: [
        vue()
    ],
    build: {
        lib: {
            entry: path.resolve(__dirname, 'src/index.js'),
            name: 'StatamicUI',
            formats: ['es'],
            fileName: () => 'index.js'
        },
        rollupOptions: {
            // Make sure to externalize deps that shouldn't be bundled
            external: ['vue'],
            output: {
                // Don't preserve modules - this should help with CSS inlining
                preserveModules: true,
                preserveModulesRoot: path.resolve(__dirname, '../'),
                entryFileNames: '[name].js',
                // Use a single entry file
                // entryFileNames: 'index.js'
            }
        },
        outDir: 'dist',
        emptyOutDir: true
    },
    resolve: {
        alias: {
            // Resolve the @ alias used in the UI components to the correct path
            '@': path.resolve(__dirname, '../..'),
            '@ui': path.resolve(__dirname, './src'),
        }
    },
    css: {
        postcss: {
            plugins: []
        }
    }
});
