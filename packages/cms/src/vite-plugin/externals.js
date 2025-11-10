import path from 'path';
import fs from 'fs';

export default function() {
    return {
        name: 'statamic-externals',

        config(config, { command }) {
            // Ensure rollupOptions exists
            config.build = config.build || {};
            config.build.rollupOptions = config.build.rollupOptions || {};

            // Add Vue as external
            config.build.rollupOptions.external = [
                ...(config.build.rollupOptions.external ?? []),
                'vue'
            ];

            return config;
        },

        configResolved(resolvedConfig) {
            resolvedConfig.build.rollupOptions.plugins = resolvedConfig.build.rollupOptions.plugins || [];
            resolvedConfig.build.rollupOptions.plugins.push({
                name: 'statamic-externals',
                renderChunk(code, chunk) {
                    // Handle mixed imports: import Default, { named } from 'vue'
                    code = code.replace(
                        /import\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*,\s*(\{[^}]+\})\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;\nconst $2 = window.Vue;'
                    );

                    // Handle remaining imports (default or named only)
                    return code.replace(
                        /import\s+(.+?)\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;'
                    );
                }
            });
        }
    };
}
