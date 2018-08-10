<template>
    <div class="yaml-fieldtype-wrapper">
        <span>YAML</span>
        <div class="editor" ref="codemirror"></div>
    </div>
</template>

<script>
var CodeMirror = require('codemirror');
require('codemirror/mode/yaml/yaml');

export default {

    mixins: [Fieldtype],

    data() {
        return {
            codemirror: null       // The CodeMirror instance
        }
    },

    mounted() {
        this.codemirror = CodeMirror(this.$refs.codemirror, {
            value: this.data || this.config.default || '',
            mode: 'yaml',
            lineNumbers: true,
            lineWrapping: true,
            viewportMargin: Infinity
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
