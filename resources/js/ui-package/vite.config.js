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
            entry: path.resolve(__dirname, 'index.js'),
            name: 'StatamicUI',
            formats: ['es', 'cjs'],
            fileName: (format) => `index.${format === 'es' ? 'js' : 'cjs'}`
        },
        rollupOptions: {
            // Make sure to externalize deps that shouldn't be bundled
            external: ['vue'],
            output: {
                globals: {
                    vue: 'Vue'
                },
                // Separate CSS file
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name === 'style.css') return 'style.css';
                    return assetInfo.name;
                }
            }
        },
        // Build CSS alongside JS
        cssCodeSplit: false,
        outDir: 'dist',
        emptyOutDir: true
    },
    resolve: {
        alias: {
            // Resolve the @ alias used in the UI components to the correct path
            '@': path.resolve(__dirname, '../')
        }
    },
    css: {
        postcss: {
            plugins: []
        }
    }
});