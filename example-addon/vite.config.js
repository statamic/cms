import laravel from 'laravel-vite-plugin'
import { defineConfig, loadEnv } from 'vite'
import statamic from './vendor/statamic/cms/resources/js/vite-plugin';
import vue from '@vitejs/plugin-vue'

export default defineConfig(({ command, mode }) => {
    const env = loadEnv(mode, process.cwd(), '')
    return {
        plugins: [
            statamic(),
            laravel({
                refresh: true,
                input: [
                    'resources/js/addon.js',
                ]
            }),
            vue()
        ]
    }
});
