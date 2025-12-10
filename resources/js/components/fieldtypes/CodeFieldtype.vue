<template>
    <CodeEditor
        ref="codeEditor"
        :theme="config.theme"
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
        };
    },

    computed: {
        mode() {
            return this.value.mode || this.config.mode;
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

    methods: {
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
