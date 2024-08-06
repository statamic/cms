<template>
    <div class="yaml-fieldtype-container relative">
        <div class="code-mode select-none">YAML</div>
        <div ref="codemirror"></div>
    </div>
</template>

<script>
import CodeMirror from 'codemirror'
import 'codemirror/mode/yaml/yaml'
import { markRaw } from 'vue';
import Fieldtype from './Fieldtype.vue';

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
        this.codemirror = markRaw(CodeMirror(this.$refs.codemirror, {
            value: this.modelValue || '',
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
        }));

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
