<template>
    <div class="code-fieldtype-container" :class="themeClass">
        <select-input v-if="config.mode_selectable" :options="modes" v-model="mode" class="code-mode-picker" />
        <div v-else v-text="modeLabel" class="code-mode"></div>
        <div ref="codemirror"></div>
    </div>
</template>

<script>
import CodeMirror from 'codemirror'

// Addons
import 'codemirror/addon/edit/matchbrackets'

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
            mode: this.value.mode || this.config.mode
        }
    },

    computed: {
        modeLabel() {
            var label = this.config.mode.replace('text/x-', '')
            return label.replace('htmlmixed', 'html');
        },
        exactTheme() {
            return (this.config.theme === 'light') ? 'default' : 'material'
        },
        themeClass() {
            return 'theme-' + this.config.theme;
        },
        replicatorPreview() {
            return this.value.code ? this.value.code.replace('<', '&lt;') : '';
        },
        readOnlyOption() {
            return this.isReadOnly ? 'nocursor' : false;
        }
    },

    mounted() {
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
        });

        this.codemirror.on('change', (cm) => {
            this.updateDebounced({code: cm.doc.getValue(), mode: this.mode});
        });

        this.codemirror.on('focus', () => this.$emit('focus'));
        this.codemirror.on('blur', () => this.$emit('blur'));


        // Refresh to ensure CodeMirror visible and the proper size
        // Most applicable when loaded by another field like Bard
        this.refresh();

        // CodeMirror also needs to be manually refreshed when made visible in the DOM
        this.$events.$on('tab-switched', this.refresh);
    },

    watch: {
        value(value, oldValue) {
            if (value.code == this.codemirror.doc.getValue()) return;
            this.codemirror.doc.setValue(value.code);
        },
        readOnlyOption(val) {
            this.codemirror.setOption('readOnly', val);
        },
        mode(mode) {
            this.codemirror.setOption('mode', mode);
            this.updateDebounced({code: this.value.code, mode: this.mode});
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
        }
    }
};
</script>
