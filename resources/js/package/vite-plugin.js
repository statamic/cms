import vue from '@vitejs/plugin-vue';
import { spawn } from 'child_process';
import { readFileSync, existsSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const statamic = function (options) {
    const { excludeStatamicClasses = true } = options;

    return {
        name: 'statamic',

        config(config, { command }) {
            if (command === 'serve' && !process.env.STATAMIC_FORCE_SERVE) {
                console.log('\x1b[33m[Statamic] Vite dev server current not supported. Automatically running "vite build --watch" instead...\x1b[0m');
                console.log('\x1b[90m[Statamic] Use STATAMIC_FORCE_SERVE=1 to bypass this behavior.\x1b[0m');

                const child = spawn('npx', ['vite', 'build', '--watch'], {
                    stdio: 'inherit',
                    cwd: process.cwd()
                });

                child.on('error', (err) => {
                    console.error('Failed to start vite build --watch:', err);
                    process.exit(1);
                });

                process.exit(0);
            }

            // Ensure rollupOptions exists
            config.build = config.build || {};
            config.build.rollupOptions = config.build.rollupOptions || {};
            config.build.rollupOptions.external = config.build.rollupOptions.external || [];
            config.build.rollupOptions.output = config.build.rollupOptions.output || {};

            // Add Vue as external
            const existingExternal = config.build.rollupOptions.external;
            config.build.rollupOptions.external = [...existingExternal, 'vue'];

            return config;
        },

        configResolved(resolvedConfig) {
            resolvedConfig.build.rollupOptions.plugins = resolvedConfig.build.rollupOptions.plugins || [];
            resolvedConfig.build.rollupOptions.plugins.push({
                name: 'statamic-global-externals',
                renderChunk(code, chunk) {
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]vue['"];?/g, 'const $1 = window.Vue;');
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]@statamic\/cms['"];?/g, 'const $1 = window.__STATAMIC__.core;');
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]@statamic\/cms\/ui['"];?/g, 'const $1 = window.__STATAMIC__.ui;');
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]@statamic\/cms\/bard['"];?/g, 'const $1 = window.__STATAMIC__.bard;');
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]@statamic\/cms\/save-pipeline['"];?/g, 'const $1 = window.__STATAMIC__.savePipeline;');
                    code = code.replace(/import\s+(.+?)\s+from\s+['"]@statamic\/cms\/temporary['"];?/g, 'const $1 = window.__STATAMIC__.temporary;');

                    return code;
                }
            });
        },

        load(id) {
            // Inject Tailwind exclusions into CSS files that request them
            if (excludeStatamicClasses && id.endsWith('.css')) {
                // Read the original CSS file
                if (existsSync(id)) {
                    const originalCSS = readFileSync(id, 'utf8');
                    // Look for the explicit opt-in directive
                    if (originalCSS.includes('@source not statamic;')) {
                        console.log('\x1b[36m[Statamic] Injecting Tailwind exclusions into CSS\x1b[0m');
                        
                        // Read the exclusions file
                        const __dirname = dirname(fileURLToPath(import.meta.url));
                        const exclusionsPath = join(__dirname, 'tailwind-exclusions.css');
                        const exclusions = readFileSync(exclusionsPath, 'utf8');
                        
                        // Replace the directive with the actual exclusions
                        return originalCSS.replace(
                            '@source not statamic;',
                            exclusions
                        );
                    }
                }
            }
            return null;
        }
    };
};

export default function (options = {}) {
    return [
        statamic(options),
        vue(options.vue || {}),
    ];
}
