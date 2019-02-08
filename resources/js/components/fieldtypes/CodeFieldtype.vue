<template>
    <div class="code-fieldtype-container relative">
        <div v-text="mode" class="code-mode"></div>
        <div ref="codemirror"></div>
    </div>
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
            codemirror: null
        }
    },

    computed: {
        mode() {
            return this.config.mode || 'php';
        }
    },

    mounted() {
        this.codemirror = CodeMirror(this.$refs.codemirror, {
            value: this.value || this.config.default || '',
            mode: this.mode,
            tabSize: 4,
            indentUnit: 4,
            indentWithTabs: true,
            lineNumbers: true,
            lineWrapping: true,
            theme: this.config.theme || 'material',
        });

        this.codemirror.on('change', (cm) => {
            this.$emit('updated', cm.doc.getValue());
        });
    },

    methods: {
        focus() {
            this.codemirror.focus();
        }
    }

};
</script>
