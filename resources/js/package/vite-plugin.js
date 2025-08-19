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

            // Add Vue and all Statamic modules as external
            const existingExternal = config.build.rollupOptions.external;
            config.build.rollupOptions.external = [
                ...existingExternal,
                'vue',
                // Match @statamic/cms and any subpath
                /^@statamic\/cms(\/.*)?$/,
            ];

            // Set up globals for browser usage
            const existingGlobals = config.build.rollupOptions.output.globals || {};
            config.build.rollupOptions.output.globals = {
                ...existingGlobals,
                'vue': 'Vue',
                '@statamic/cms': '__STATAMIC__.core',
                '@statamic/cms/ui': '__STATAMIC__.ui',
                '@statamic/cms/bard': '__STATAMIC__.bard',
                '@statamic/cms/save-pipeline': '__STATAMIC__.savePipeline',
            };

            // Set default format if not specified
            if (!config.build.rollupOptions.output.format) {
                config.build.rollupOptions.output.format = 'iife';
            }

            return config;
        }
    };
};

export default function (options = {}) {
    return [
        statamic(options),
        vue(options.vue || {}),
    ];
}
