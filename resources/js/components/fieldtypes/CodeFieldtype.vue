<template>

<portal name="code-fullscreen" :disabled="!fullScreenMode" target-class="code-fieldtype">
<element-container @resized="refresh">
    <div class="code-fieldtype-container" :class="[themeClass, {'code-fullscreen': fullScreenMode }]">
        <publish-field-fullscreen-header
            v-if="fullScreenMode"
            :title="config.title"
            :field-actions="visibleFieldActions"
            @close="toggleFullscreen">
            <div class="code-fieldtype-toolbar-fullscreen">
                <div>
                    <select-input v-if="config.mode_selectable" :options="modes" v-model="mode" :is-read-only="isReadOnly" class="text-xs leading-none" />
                    <div v-else v-text="modeLabel" class="text-xs font-mono text-gray-700"></div>
                </div>
            </div>
        </publish-field-fullscreen-header>
        <div class="code-fieldtype-toolbar" v-if="!fullScreenMode">
            <div>
                <select-input v-if="config.mode_selectable" :options="modes" v-model="mode" :is-read-only="isReadOnly" class="text-xs leading-none" />
                <div v-else v-text="modeLabel" class="text-xs font-mono text-gray-700"></div>
            </div>
        </div>
        <div ref="codemirror"></div>
    </div>
</element-container>
</portal>

</template>

<script>
import CodeMirror from 'codemirror'

// Addons
import 'codemirror/addon/edit/matchbrackets'
import 'codemirror/addon/display/fullscreen'
import 'codemirror/addon/display/rulers'

// Keymaps
import 'codemirror/keymap/sublime'
import 'codemirror/keymap/vim'

// Modes
import 'codemirror/mode/css/css'
import 'codemirror/mode/clike/clike'
import 'codemirror/mode/diff/diff'
import 'codemirror/mode/go/go'
import 'codemirror/mode/gfm/gfm'
import 'codemirror/mode/handlebars/handlebars'
import 'codemirror/mode/haml/haml'
import 'codemirror/mode/htmlmixed/htmlmixed'
import 'codemirror/mode/javascript/javascript'
import 'codemirror/mode/markdown/markdown'
import 'codemirror/mode/nginx/nginx'
import 'codemirror/mode/php/php'
import 'codemirror/mode/python/python'
import 'codemirror/mode/ruby/ruby'
import 'codemirror/mode/shell/shell'
import 'codemirror/mode/sql/sql'
import 'codemirror/mode/twig/twig'
import 'codemirror/mode/vue/vue'
import 'codemirror/mode/xml/xml'
import 'codemirror/mode/yaml/yaml'
import 'codemirror/mode/yaml-frontmatter/yaml-frontmatter'

export default {

    mixins: [Fieldtype],

    data() {
        return {
            codemirror: null,
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
            mode: this.value.mode || this.config.mode,
            fullScreenMode: false,
        }
    },

    computed: {
        modeLabel() {
            return _.findWhere(this.modes, { value: this.mode }).label || this.mode;
        },
        exactTheme() {
            return (this.config.theme === 'light') ? 'default' : 'material'
        },
        themeClass() {
            return 'theme-' + this.config.theme;
        },
        replicatorPreview() {
            if (! this.showFieldPreviews || ! this.config.replicator_preview) return;

            return this.value.code ? truncate(this.value.code, 60) : '';
        },
        readOnlyOption() {
            return this.isReadOnly ? 'nocursor' : false;
        },
        rulers() {
            if (!this.config.rulers) {
                return [];
            }

            let rulerColor = (this.config.theme === 'light')
                ? '#d1d5db'
                : '#546e7a';

            return Object.entries(this.config.rulers).map(([column, style]) => {
                let lineStyle = style === 'dashed' ? 'dashed' : 'solid';

                return {
                    column: parseInt(column),
                    lineStyle: lineStyle,
                    color: rulerColor,
                };
            });
        },
        internalFieldActions() {
            return [
                {
                    title: __('Toggle Fullscreen Mode'),
                    icon: ({ vm }) => vm.fullScreenMode ? 'shrink-all' : 'expand-bold',
                    quick: true,
                    run: this.toggleFullscreen,
                },
            ];
        },
    },

    watch: {
        value(value, oldValue) {
            if (value.code == this.codemirror.doc.getValue()) return;
            if (! value.code) value.code = '';

            this.codemirror.doc.setValue(value.code);
        },
        readOnlyOption(val) {
            this.codemirror.setOption('readOnly', val);
        },
        mode(mode) {
            this.codemirror.setOption('mode', mode);
            this.updateDebounced({code: this.value.code, mode: this.mode});
        },
        fullScreenMode: {
            immediate: true,
            handler: function (fullscreen) {
                this.$nextTick(() => {
                    this.$nextTick(() => this.initCodeMirror());
                });
            }
        },
    },

    methods: {
        focus() {
            this.codemirror.focus();
        },
        refresh() {
            this.$nextTick(function() {
                this.codemirror.refresh();
            })
        },
        initCodeMirror() {
            this.codemirror = CodeMirror(this.$refs.codemirror, {
                value: this.value.code || '',
                mode: this.mode,
                direction: document.querySelector('html').getAttribute('dir') ?? 'ltr',
                addModeClass: true,
                keyMap: this.config.key_map,
                tabSize: this.config.indent_size,
                indentWithTabs: this.config.indent_type !== 'spaces',
                lineNumbers: this.config.line_numbers,
                lineWrapping: this.config.line_wrapping,
                matchBrackets: true,
                readOnly: this.readOnlyOption,
                theme: this.exactTheme,
                inputStyle: 'contenteditable',
                rulers: this.rulers,
            });

            this.codemirror.on('change', (cm) => {
                this.updateDebounced({code: cm.doc.getValue(), mode: this.mode});
            });

            this.codemirror.on('focus', () => this.$emit('focus'));
            this.codemirror.on('blur', () => this.$emit('blur'));


            // Refresh to ensure CodeMirror visible and the proper size
            // Most applicable when loaded by another field like Bard
            this.refresh();

            this.codemirror.setOption('fullScreen', this.fullScreenMode);

            if (this.fullScreenMode === false) {
                document.documentElement.removeAttribute('style');
            }

            // CodeMirror also needs to be manually refreshed when made visible in the DOM
            this.$events.$on('tab-switched', this.refresh);
        },
        toggleFullscreen() {
            this.fullScreenMode = !this.fullScreenMode;
        },
    }
};
</script>
