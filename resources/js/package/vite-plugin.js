import vue from '@vitejs/plugin-vue';

const statamic = function (options) {
    return {
        name: 'statamic',

        config(config) {
            // Ensure rollupOptions exists
            config.build = config.build || {};
            config.build.rollupOptions = config.build.rollupOptions || {};
            config.build.rollupOptions.external = config.build.rollupOptions.external || [];
            config.build.rollupOptions.output = config.build.rollupOptions.output || {};

            return config;
        },

        configResolved(resolvedConfig) {
            const inputs = resolvedConfig.build?.rollupOptions?.input;
            const hasMultipleInputs = (Array.isArray(inputs) && inputs.length > 1) ||
                (typeof inputs === 'object' && inputs !== null && !Array.isArray(inputs) && Object.keys(inputs).length > 1);

            if (hasMultipleInputs) {
                // For multiple inputs, just disable inlineDynamicImports
                resolvedConfig.build.rollupOptions.output.inlineDynamicImports = false;
            } else {
                // Single input - set up as external modules for addon development
                const existingExternal = resolvedConfig.build.rollupOptions.external;
                resolvedConfig.build.rollupOptions.external = [
                    ...existingExternal,
                    'vue',
                    // Match @statamic/cms and any subpath
                    /^@statamic\/cms(\/.*)?$/,
                ];

                // Set up globals for browser usage
                const existingGlobals = resolvedConfig.build.rollupOptions.output.globals || {};
                resolvedConfig.build.rollupOptions.output.globals = {
                    ...existingGlobals,
                    'vue': 'Vue',
                    '@statamic/cms': '__STATAMIC__.core',
                    '@statamic/cms/ui': '__STATAMIC__.ui',
                    '@statamic/cms/bard': '__STATAMIC__.bard',
                    '@statamic/cms/save-pipeline': '__STATAMIC__.savePipeline',
                };

                // Set default format if not specified
                if (!resolvedConfig.build.rollupOptions.output.format) {
                    resolvedConfig.build.rollupOptions.output.format = 'iife';
                }
            }
        }
    };
};

export default function (options = {}) {
    return [
        statamic(options),
        vue(options.vue || {}),
    ];
}
