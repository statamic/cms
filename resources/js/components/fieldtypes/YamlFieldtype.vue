<template>
    <div class="yaml-fieldtype-wrapper">
        <span>YAML</span>
        <div class="editor" v-el:codemirror></div>
    </div>
</template>

<script>
var CodeMirror = require('codemirror');
require('codemirror/mode/yaml/yaml');

module.exports = {

    mixins: [Fieldtype],

    data() {
        return {
            codemirror: null       // The CodeMirror instance
        }
    },

    ready: function() {
        this.codemirror = CodeMirror(this.$els.codemirror, {
            value: this.data || '',
            mode: 'yaml',
            lineNumbers: true,
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
