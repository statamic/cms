<template>
    <div ref="codemirror"></div>
</template>

<style src="codemirror/theme/material.css" />

<script>
import CodeMirror from 'codemirror'

// Modes
import 'codemirror/mode/css/css'
import 'codemirror/mode/go/go'
import 'codemirror/mode/gfm/gfm'
import 'codemirror/mode/haml/haml'
import 'codemirror/mode/htmlmixed/htmlmixed'
import 'codemirror/mode/javascript/javascript'
import 'codemirror/mode/markdown/markdown'
import 'codemirror/mode/php/php'
import 'codemirror/mode/python/python'
import 'codemirror/mode/ruby/ruby'
import 'codemirror/mode/sass/sass'
import 'codemirror/mode/shell/shell'
import 'codemirror/mode/twig/twig'
import 'codemirror/keymap/vim'
import 'codemirror/mode/vue/vue'
import 'codemirror/mode/xml/xml'
import 'codemirror/mode/yaml/yaml'
import 'codemirror/mode/yaml-frontmatter/yaml-frontmatter'

export default {

    mixins: [Fieldtype],

    data() {
        return {
            data: this.value,
            codemirror: null
        }
    },

    computed: {
        mode() {
            return this.config.mode || 'php';
        }
    },

    watch: {
        data(value) {
            this.update(value);
        }
    },

    mounted() {
        this.codemirror = CodeMirror(this.$refs.codemirror, {
            value: this.data || this.config.default || '',
            mode: this.mode,
            tabSize: 4,
            indentUnit: 4,
            indentWithTabs: true,
            lineNumbers: true,
            lineWrapping: true,
            theme: this.config.theme || 'material',
        });

        this.codemirror.on('change', (cm) => {
            this.data = cm.doc.getValue();
        });
    },

    methods: {
        focus() {
            this.codemirror.focus();
        }
    }

};
</script>

<style>
.CodeMirror {
    min-height: 80px;
    line-height: 1.75;
    font-size: 13px !important;
}

.CodeMirror-wrap {
    padding: 0.5rem;
    border-radius: 4px;
}

.CodeMirror-scroll {
    height: auto;
}
</style>
