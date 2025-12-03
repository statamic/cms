import * as Vue from 'vue';

export default function() {
    const VIRTUAL_MODULE_ID = 'vue';
    const RESOLVED_VIRTUAL_MODULE_ID = '\0vue-external';
    const vueExports = Object.keys(Vue).filter(key => key !== 'default');

    return {
        name: 'statamic-externals',
        enforce: 'pre',

        resolveId(id) {
            if (id === 'vue') {
                return RESOLVED_VIRTUAL_MODULE_ID;
            }
            return null;
        },

        load(id) {
            if (id === RESOLVED_VIRTUAL_MODULE_ID) {
                const exportsList = vueExports.join(', ');
                return `
                    const Vue = window.Vue;
                    export default Vue;
                    export const { ${exportsList} } = Vue;
                `;
            }
            return null;
        },

        config(config, { command }) {
            config.resolve = config.resolve || {};
            config.resolve.alias = config.resolve.alias || {};

            if (command === 'build') {
                config.build = config.build || {};
                config.build.rollupOptions = config.build.rollupOptions || {};

                config.build.rollupOptions.external = [
                    ...(config.build.rollupOptions.external ?? []),
                    'vue'
                ];

                config.build.rollupOptions.output = config.build.rollupOptions.output || {};
                config.build.rollupOptions.output.globals = {
                    ...(config.build.rollupOptions.output.globals ?? {}),
                    'vue': 'window.Vue'
                };
            }

            return config;
        },

        configResolved(resolvedConfig) {
            resolvedConfig.build.rollupOptions.plugins = resolvedConfig.build.rollupOptions.plugins || [];
            resolvedConfig.build.rollupOptions.plugins.push({
                name: 'statamic-externals-transform',
                renderChunk(code) {
                    code = code.replace(
                        /import\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*,\s*(\{[^}]+\})\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;\nconst $2 = window.Vue;',
                    );
                    code = code.replace(
                        /import\s+(.+?)\s+from\s+['"]vue['"];?/g,
                        'const $1 = window.Vue;',
                    );
                    return code;
                },
            });
        }
    };
}
