<script setup>
import CodeMirror from 'codemirror';
import { computed, markRaw, nextTick, onMounted, ref, useAttrs, useTemplateRef, watch } from 'vue';
import ElementContainer from '@statamic/components/ElementContainer.vue';

// Addons
import 'codemirror/addon/edit/matchbrackets';
import 'codemirror/addon/display/fullscreen';
import 'codemirror/addon/display/rulers';

// Keymaps
import 'codemirror/keymap/sublime';
import 'codemirror/keymap/vim';

// Modes
import 'codemirror/mode/css/css';
import 'codemirror/mode/clike/clike';
import 'codemirror/mode/diff/diff';
import 'codemirror/mode/go/go';
import 'codemirror/mode/gfm/gfm';
import 'codemirror/mode/handlebars/handlebars';
import 'codemirror/mode/haml/haml';
import 'codemirror/mode/htmlmixed/htmlmixed';
import 'codemirror/mode/javascript/javascript';
import 'codemirror/mode/markdown/markdown';
import 'codemirror/mode/nginx/nginx';
import 'codemirror/mode/php/php';
import 'codemirror/mode/python/python';
import 'codemirror/mode/ruby/ruby';
import 'codemirror/mode/shell/shell';
import 'codemirror/mode/sql/sql';
import 'codemirror/mode/twig/twig';
import 'codemirror/mode/vue/vue';
import 'codemirror/mode/xml/xml';
import 'codemirror/mode/yaml/yaml';
import 'codemirror/mode/yaml-frontmatter/yaml-frontmatter';

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const props = defineProps({
    mode: {
        type: String,
        required: true,
    },
    theme: {
        type: String,
        default: 'material',
    },
    rulers: {
        type: Object,
        default: () => {},
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    keyMap: {
        type: String,
        default: 'sublime',
    },
    tabSize: {
        type: Number,
        required: false,
    },
    indentType: {
        type: String,
        default: 'tabs',
    },
    lineNumbers: {
        type: Boolean,
        default: true,
    },
    lineWrapping: {
        type: Boolean,
        default: true,
    },
    modelValue: String,
});

const codemirror = ref(null);
const codemirrorElement = useTemplateRef('codemirror');

defineOptions({
    inheritAttrs: false,
});

defineExpose({
    codemirror,
    refresh,
})

onMounted(() => {
    nextTick(() => initCodeMirror());
});

function initCodeMirror() {
    codemirror.value = markRaw(
        CodeMirror(codemirrorElement.value, {
            value: props.modelValue || '',
            mode: props.mode,
            direction: document.querySelector('html').getAttribute('dir') ?? 'ltr',
            addModeClass: true,
            keyMap: props.keyMap,
            tabSize: props.tabSize,
            indentWithTabs: props.indentType !== 'spaces',
            lineNumbers: props.lineNumbers,
            lineWrapping: props.lineWrapping,
            matchBrackets: true,
            readOnly: props.disabled ? 'nocursor' : false,
            theme: exactTheme.value,
            inputStyle: 'contenteditable',
            rulers: rulers,
        }),
    );

    codemirror.value.on('change', (cm) => {
        emit('update:modelValue', cm.doc.getValue());
    });

    codemirror.value.on('focus', () => emit('focus'));
    codemirror.value.on('blur', () => emit('blur'));

    // Refresh to ensure CodeMirror visible and the proper size
    // Most applicable when loaded by another field like Bard
    refresh();
}

function refresh() {
    nextTick(() => codemirror.value.refresh());
}

watch(
    () => props.mode,
    (value) => {
        codemirror.value?.setOption('mode', value);
    },
    { immediate: true }
);

watch(
    () => props.disabled,
    (value) => {
        codemirror.value?.setOption('readOnly', value ? 'nocursor' : false);
    },
    { immediate: true }
);

watch(
    () => props.modelValue,
    (value) => {
        if (value === codemirror.value?.doc.getValue()) return;
        if (!value) value = '';

        codemirror.value?.doc.setValue(value);
    },
    { immediate: true }
);

const exactTheme = computed(() => {
    return props.theme === 'light' ? 'default' : 'material';
});

const rulers = computed(() => {
    if (! props.rulers) {
        return [];
    }

    let rulerColor = props.theme === 'light' ? '#d1d5db' : '#546e7a';

    return Object.entries(props.rulers).map(([column, style]) => {
        let lineStyle = style === 'dashed' ? 'dashed' : 'solid';

        return {
            column: parseInt(column),
            lineStyle: lineStyle,
            color: rulerColor,
        };
    });
});
</script>

<template>
    <ElementContainer @resized="refresh">
        <div ref="codemirror"></div>
    </ElementContainer>
</template>
