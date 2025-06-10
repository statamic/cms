<template>
    <CodeEditor
        ref="codeEditor"
        :theme="config.theme"
        :rulers="config.rulers"
        :disabled="isReadOnly"
        :key-map="config.key_map"
        :tab-size="config.indent_size"
        :indent-type="config.indent_type"
        :line-numbers="config.line_numbers"
        :line-wrapping="config.line_wrapping"
        :allow-mode-selection="config.mode_selectable"
        :mode="mode"
        :model-value="value.code"
        @update:mode="modeUpdated"
        @update:model-value="codeUpdated"
    />
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { CodeEditor } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: { CodeEditor },

    computed: {
        mode() {
            return this.value.mode || this.config.mode;
        },

        replicatorPreview() {
            if (!this.showFieldPreviews || !this.config.replicator_preview) return;

            return this.value.code ? truncate(this.value.code, 60) : '';
        },

        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => (vm.fullScreenMode ? 'shrink-all' : 'expand-bold'),
                    quick: true,
                    visibleWhenReadOnly: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
    },

    mounted() {
        // CodeMirror needs to be manually refreshed when made visible in the DOM.
        this.$events.$on('tab-switched', () => this.$refs.codeEditor?.refresh());
    },

    methods: {
        modeUpdated(mode) {
            this.updateDebounced({ code: this.value.code, mode });
        },

        codeUpdated(code) {
            this.updateDebounced({ code, mode: this.mode });
        },
    },
};
</script>
