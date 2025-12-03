export default function() {
    const VIRTUAL_MODULE_ID = 'vue';
    const RESOLVED_VIRTUAL_MODULE_ID = '\0vue-external';

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
                return `
                    const Vue = window.Vue;
                    export default Vue;
                    export const {
                        createApp,
                        ref,
                        reactive,
                        computed,
                        watch,
                        watchEffect,
                        onMounted,
                        onUnmounted,
                        onBeforeMount,
                        onBeforeUnmount,
                        onUpdated,
                        onBeforeUpdate,
                        nextTick,
                        defineComponent,
                        defineAsyncComponent,
                        h,
                        toRefs,
                        toRef,
                        unref,
                        isRef,
                        resolveComponent,
                        createElementBlock,
                        openBlock,
                        createTextVNode,
                        createVNode,
                        toDisplayString,
                        withCtx,
                    } = Vue;

                    export const __VUE_HMR_RUNTIME__ = window.__VUE_HMR_RUNTIME__;
                `;
            }
            return null;
        },

        config(config, { command }) {
            config.resolve = config.resolve || {};
            config.resolve.alias = config.resolve.alias || {};

            if (!Array.isArray(config.resolve.alias)) {
                config.resolve.alias = {
                    ...config.resolve.alias,
                    'vue': VIRTUAL_MODULE_ID
                };
            } else {
                config.resolve.alias.push({
                    find: 'vue',
                    replacement: VIRTUAL_MODULE_ID
                });
            }

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
            if (resolvedConfig.command === 'build') {
                resolvedConfig.build.rollupOptions.plugins = resolvedConfig.build.rollupOptions.plugins || [];
                resolvedConfig.build.rollupOptions.plugins.push({
                    name: 'statamic-externals-transform',
                    renderChunk(code) {
                        code = code.replace(
                            /import\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*,\s*(\{[^}]+\})\s+from\s+['"]vue['"];?/g,
                            'const $1 = window.Vue;\nconst $2 = window.Vue;'
                        );

                        code = code.replace(
                            /import\s+(.+?)\s+from\s+['"]vue['"];?/g,
                            'const $1 = window.Vue;'
                        );

                        return code;
                    }
                });
            }
        }
    };
}
