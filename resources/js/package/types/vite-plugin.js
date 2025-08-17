import path from 'path';
import { fileURLToPath } from 'url';

const statamicModules = {
    '@statamic/cms': 'StatamicCms',
    '@statamic/cms/ui': 'StatamicCms.ui',
    '@statamic/cms/bard': 'StatamicCms.bard',
    '@statamic/cms/save-pipeline': 'StatamicCms.savePipeline',
    // Add more as your package grows
};

export default function () {
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
                // 'vue',
                // Match @statamic/cms and any subpath
                /^@statamic\/cms(\/.*)?$/,
            ];

            // Set up globals for browser usage
            const existingGlobals = config.build.rollupOptions.output.globals || {};
            config.build.rollupOptions.output.globals = {
                ...existingGlobals,
                'vue': 'Vue',
                ...statamicModules,
            };

            // Set default format if not specified
            if (!config.build.rollupOptions.output.format) {
                config.build.rollupOptions.output.format = 'iife';
            }

            return config;
        }
    };
}
