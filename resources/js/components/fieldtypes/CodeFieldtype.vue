<template>
    <portal name="code-fullscreen" :disabled="!fullScreenMode" target-class="code-fieldtype">
            <div class="code-fieldtype-container" :class="[themeClass, { 'code-fullscreen': fullScreenMode }]">
                <publish-field-fullscreen-header
                    v-if="fullScreenMode"
                    :title="config.display"
                    :field-actions="fieldActions"
                    @close="toggleFullscreen"
                >
                    <div class="code-fieldtype-toolbar-fullscreen">
                        <div>
                            <Select
                                class="w-full"
                                v-if="config.mode_selectable"
                                :options="modes"
                                :disabled="isReadOnly"
                                :model-value="mode"
                                @update:modelValue="modeUpdated"
                            />
                            <div v-else v-text="modeLabel" class="font-mono text-xs text-gray-700"></div>
                        </div>
                    </div>
                </publish-field-fullscreen-header>
                <div class="code-fieldtype-toolbar" v-if="!fullScreenMode">
                    <div>
                        <Select
                            class="w-full"
                            v-if="config.mode_selectable"
                            :options="modes"
                            :disabled="isReadOnly"
                            :model-value="mode"
                            @update:modelValue="modeUpdated"
                        />

                        <div v-else v-text="modeLabel" class="font-mono text-xs text-gray-700"></div>
                    </div>
                </div>
                <CodeEditor
                    :mode="mode"
                    :modes="modes"
                    :theme="config.theme"
                    :rulers="config.rulers"
                    :disabled="isReadOnly"
                    :key-map="config.key_map"
                    :tab-size="config.indent_size"
                    :indent-type="config.indent_type"
                    :line-numbers="config.line_numbers"
                    :line-wrapping="config.line_wrapping"
                    :model-value="value.code"
                    @update:model-value="codeUpdated"
                />
            </div>
    </portal>
</template>

<script>
import Fieldtype from './Fieldtype.vue';
import { Select, CodeEditor } from '@statamic/ui';

export default {
    mixins: [Fieldtype],

    components: { Select, CodeEditor },

    data() {
        return {
            modes: [
                { value: 'clike', label: 'C-Like' },
                { value: 'css', label: 'CSS' },
                { value: 'diff', label: 'Diff' },
                { value: 'go', label: 'Go' },
                { value: 'haml', label: 'HAML' },
                { value: 'handlebars', label: 'Handlebars' },
                { value: 'htmlmixed', label: 'HTML' },
                { value: 'less', label: 'LESS' },
                { value: 'markdown', label: 'Markdown' },
                { value: 'gfm', label: 'Markdown (GHF)' },
                { value: 'nginx', label: 'Nginx' },
                { value: 'text/x-java', label: 'Java' },
                { value: 'javascript', label: 'JavaScript' },
                { value: 'jsx', label: 'JSX' },
                { value: 'text/x-objectivec', label: 'Objective-C' },
                { value: 'php', label: 'PHP' },
                { value: 'python', label: 'Python' },
                { value: 'ruby', label: 'Ruby' },
                { value: 'scss', label: 'SCSS' },
                { value: 'shell', label: 'Shell' },
                { value: 'sql', label: 'SQL' },
                { value: 'twig', label: 'Twig' },
                { value: 'vue', label: 'Vue' },
                { value: 'xml', label: 'XML' },
                { value: 'yaml-frontmatter', label: 'YAML' },
            ],
            fullScreenMode: false,
        };
    },

    computed: {
        mode() {
            return this.value.mode || this.config.mode;
        },

        modeLabel() {
            return this.modes.find((m) => m.value === this.mode).label || this.mode;
        },

        themeClass() {
            return `theme-${this.config.theme}`;
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

    methods: {
        modeUpdated(mode) {
            this.updateDebounced({ code: this.value.code, mode });
        },

        codeUpdated(code) {
            this.updateDebounced({ code, mode: this.mode });
        },

        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },
    },
};
</script>
