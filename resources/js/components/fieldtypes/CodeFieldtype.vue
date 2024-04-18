<template>

<portal name="code-fullscreen" :disabled="!fullScreenMode" target-class="code-fieldtype">
<element-container @resized="refresh">
    <div class="code-fieldtype-container" :class="[themeClass, {'code-fullscreen': fullScreenMode }]">
        <div class="code-fieldtype-toolbar">
            <div>
                <select-input v-if="config.mode_selectable" :options="modes" v-model="mode" :is-read-only="isReadOnly" class="text-xs leading-none" />
                <div v-else v-text="modeLabel" class="text-xs font-mono text-gray-700"></div>
            </div>
            <button @click="fullScreenMode = !fullScreenMode" class="btn-icon h-8 leading-none flex items-center justify-center text-gray-800 dark:text-dark-150" v-tooltip="__('Toggle Fullscreen Mode')">
                <svg-icon name="expand-bold" class="h-3.5 w-3.5" v-show="!fullScreenMode" />
                <svg-icon name="arrows-shrink" class="h-3.5 w-3.5" v-show="fullScreenMode" />
            </button>
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
                { value: 'clike', label: __('C-Like') },
                { value: 'css', label: __('CSS') },
                { value: 'diff', label: __('Diff') },
                { value: 'go', label: __('Go') },
                { value: 'haml', label: __('HAML') },
                { value: 'handlebars', label: __('Handlebars') },
                { value: 'htmlmixed', label: __('HTML') },
                { value: 'less', label: __('LESS') },
                { value: 'markdown', label: __('Markdown') },
                { value: 'gfm', label: __('Markdown (GHF)') },
                { value: 'nginx', label: __('Nginx') },
                { value: 'text/x-java', label: __('Java') },
                { value: 'javascript', label: __('JavaScript') },
                { value: 'jsx', label: __('JSX') },
                { value: 'text/x-objectivec', label: __('Objective-C') },
                { value: 'php', label: __('PHP') },
                { value: 'python', label: __('Python') },
                { value: 'ruby', label: __('Ruby') },
                { value: 'scss', label: __('SCSS') },
                { value: 'shell', label: __('Shell') },
                { value: 'sql', label: __('SQL') },
                { value: 'twig', label: __('Twig') },
                { value: 'vue', label: __('Vue') },
                { value: 'xml', label: __('XML') },
                { value: 'yaml-frontmatter', label: __('YAML') },
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

            // CodeMirror also needs to be manually refreshed when made visible in the DOM
            this.$events.$on('tab-switched', this.refresh);
        }
    }
};
</script>
