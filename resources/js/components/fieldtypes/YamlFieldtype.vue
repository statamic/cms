<template>
    <div class="yaml-fieldtype-container relative">
        <div v-text="'yaml'" class="code-mode"></div>
        <div ref="codemirror"></div>
    </div>
</template>

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

    computed: {
        readOnlyOption() {
            return this.isReadOnly ? 'nocursor' : false;
        }
    },

    mounted() {
        this.codemirror = CodeMirror(this.$refs.codemirror, {
            value: this.value || '',
            mode: 'yaml',
            direction: document.querySelector('html').getAttribute('dir') ?? 'ltr',
            tabSize: 2,
            indentUnit: 2,
            autoRefresh: true,
            indentWithTabs: false,
            lineNumbers: true,
            lineWrapping: true,
            readOnly: this.readOnlyOption,
            theme: this.config.theme || 'material',
            inputStyle: 'contenteditable',
        });

        this.codemirror.on('change', (cm) => {
            this.updateDebounced(cm.doc.getValue());
        });
    },

    watch: {
        readOnlyOption(val) {
            this.codemirror.setOption('readOnly', val);
        }
    },

    methods: {
        focus() {
            this.codemirror.focus();
        }
    }

};
</script>
