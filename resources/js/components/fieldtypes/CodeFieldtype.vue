<template>
    <CodeEditor
        ref="codeEditor"
        :theme="resolvedColorMode"
        :rulers="config.rulers"
        :disabled="config.disabled"
        :read-only="config.read_only"
        :key-map="config.key_map"
        :tab-size="config.indent_size"
        :indent-type="config.indent_type"
        :line-numbers="config.line_numbers"
        :line-wrapping="config.line_wrapping"
        :allow-mode-selection="config.mode_selectable"
        :show-mode-label="config.show_mode_label"
        :mode="mode"
        :model-value="value.code"
        :title="config.display"
        :field-actions="fieldActions"
        @update:mode="modeUpdated"
        @update:model-value="codeUpdated"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { CodeEditor } from '@/components/ui';

export default {
    mixins: [Fieldtype],

    components: { CodeEditor },

    data() {
        return {
            escBinding: null,
            systemColorMode: null,
            colorModeWatcher: null,
            mediaQueryListener: null,
            mutationObserver: null,
        };
    },

    computed: {
        mode() {
            return this.value.mode || this.config.mode;
        },

        resolvedColorMode() {
            const colorMode = this.config.color_mode || 'material';
            
            if (colorMode === 'system') {
                return this.systemColorMode || this.getSystemColorMode();
            }
            
            return colorMode;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews) return;

            return this.value.code ? truncate(this.value.code, 60) : '';
        },

        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.$refs.codeEditor.fullScreenMode ? 'fullscreen-close' : 'fullscreen-open'),
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: ({ vm }) => vm.toggleFullscreen(),
                },
            ];
        },
    },

    watch: {
        'config.color_mode'() {
            if (this.config.color_mode === 'system') {
                this.updateSystemColorMode();
            }
        },
    },

    mounted() {
        this.updateSystemColorMode();
        this.watchSystemColorMode();
    },

    beforeUnmount() {
        this.cleanupWatchers();
    },

    methods: {
        getSystemColorMode() {
            const preference = Statamic.$colorMode?.preference?.value || 'auto';
            
            if (preference === 'dark') {
                return 'material';
            }
            
            if (preference === 'light') {
                return 'light';
            }
            
            // preference === 'auto' - check system preference
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'material' : 'light';
        },

        updateSystemColorMode() {
            this.systemColorMode = this.getSystemColorMode();
        },

        watchSystemColorMode() {
            // Watch for changes in the system color mode preference
            if (Statamic.$colorMode?.preference) {
                this.colorModeWatcher = this.$watch(
                    () => Statamic.$colorMode.preference.value,
                    () => {
                        if (this.config.color_mode === 'system') {
                            this.updateSystemColorMode();
                        }
                    }
                );
            }

            // Watch for system preference changes (when preference is 'auto')
            const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const handleChange = () => {
                if (this.config.color_mode === 'system') {
                    this.updateSystemColorMode();
                }
            };
            
            if (mediaQuery.addEventListener) {
                mediaQuery.addEventListener('change', handleChange);
                this.mediaQueryListener = handleChange;
            } else {
                // Fallback for older browsers
                mediaQuery.addListener(handleChange);
                this.mediaQueryListener = handleChange;
            }

            // Watch for dark class changes on document element
            this.mutationObserver = new MutationObserver(() => {
                if (this.config.color_mode === 'system') {
                    this.updateSystemColorMode();
                }
            });

            this.mutationObserver.observe(document.documentElement, {
                attributes: true,
                attributeFilter: ['class'],
            });
        },

        cleanupWatchers() {
            if (this.colorModeWatcher) {
                this.colorModeWatcher();
            }

            if (this.mediaQueryListener) {
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                if (mediaQuery.removeEventListener) {
                    mediaQuery.removeEventListener('change', this.mediaQueryListener);
                } else {
                    mediaQuery.removeListener(this.mediaQueryListener);
                }
            }

            if (this.mutationObserver) {
                this.mutationObserver.disconnect();
            }
        },

        toggleFullscreen() {
            const wasFullscreen = this.$refs.codeEditor.fullScreenMode;
            this.$refs.codeEditor.toggleFullscreen();

            if (wasFullscreen) {
                if (this.escBinding) {
                    this.escBinding.destroy();
                    this.escBinding = null;
                }
            } else {
                this.escBinding = this.$keys.bindGlobal('esc', this.toggleFullscreen);
            }
        },

        modeUpdated(mode) {
            this.updateDebounced({ code: this.value.code, mode });
        },

        codeUpdated(code) {
            this.updateDebounced({ code, mode: this.mode });
        },
    },
};
</script>
