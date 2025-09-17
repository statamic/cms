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
                    return code
                        .replace(/import\s+(.+?)\s+from\s+['"]vue['"];?/g, 'const $1 = window.Vue;');
                }
            });
        }
    };
}
