<template>
    <div class="yaml-fieldtype-container relative">
        <div v-text="'yaml'" class="code-mode"></div>
        <div ref="codemirror"></div>
    </div>
</template>

<style src="codemirror/theme/material.css">
</style>

<script>
import CodeMirror from 'codemirror'
import 'codemirror/mode/yaml/yaml'

export default {

    mixins: [Fieldtype],

    data() {
        return {
            codemirror: null
        }
    },

    mounted() {
        this.codemirror = CodeMirror(this.$refs.codemirror, {
            value: this.value || this.config.default || '',
            mode: 'yaml',
            tabSize: 2,
            indentUnit: 2,
            indentWithTabs: false,
            lineNumbers: true,
            lineWrapping: true,
            theme: this.config.theme || 'material',
        });

        this.codemirror.on('change', (cm) => {
            this.update(cm.doc.getValue());
        });
    },

    methods: {
        focus() {
            this.codemirror.focus();
        }
    }

};
</script>
