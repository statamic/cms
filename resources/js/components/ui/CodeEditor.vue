<script setup>
import CodeMirror from 'codemirror';
import { computed, markRaw, nextTick, onMounted, ref, useAttrs, useTemplateRef, watch } from 'vue';
import Select from './Select/Select.vue';
import { colorMode as colorModeApi } from '@api';

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

const emit = defineEmits(['update:mode', 'update:model-value', 'focus', 'blur']);

const props = defineProps({
    /** When `true`, displays a mode selector dropdown */
    allowModeSelection: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    fieldActions: { type: Array, default: () => [] },
    /** Controls whether to indent with tabs or spaces. Options: `tabs`, `spaces` */
    indentType: { type: String, default: 'tabs' },
    /** Keyboard mapping for the editor. Options: `sublime`, `vim` */
    keyMap: { type: String, default: 'sublime' },
    /** When `true`, line numbers are displayed */
    lineNumbers: { type: Boolean, default: true },
    /** When `true`, long lines will wrap */
    lineWrapping: { type: Boolean, default: true },
    /** The syntax highlighting mode. Options: `clike`, `css`, `diff`, `go`, `haml`, `handlebars`, `htmlmixed`, `less`, `markdown`, `gfm`, `nginx`, `text/x-java`, `javascript`, `jsx`, `text/x-objectivec`, `php`, `python`, `ruby`, `scss`, `shell`, `sql`, `twig`, `vue`, `xml`, `yaml-frontmatter` */
    mode: { type: String, default: 'javascript' },
    /** The controlled value of the code editor */
    modelValue: { type: String, default: '' },
    readOnly: { type: Boolean, default: false },
    /** Rulers configuration */
    rulers: { type: Object, default: () => {} },
    /** When `true`, displays the current mode label */
    showModeLabel: { type: Boolean, default: true },
    /** The width of a tab character */
    tabSize: { type: Number, required: false },
    /** Theme of the code editor. Options: `system`, `light`, `dark` */
    colorMode: { type: String, default: 'system' },
    /** Title displayed in fullscreen mode */
    title: { type: String, default: () => __('Code Editor') },
});

const modes = ref([
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
]);

const codemirror = ref(null);
const codemirrorElement = useTemplateRef('codemirrorElement');
const fullScreenMode = ref(false);

defineOptions({
    inheritAttrs: false,
});

defineExpose({
    toggleFullscreen,
    fullScreenMode,
});

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
            readOnly: props.readOnly || props.disabled ? 'nocursor' : false,
            theme: theme.value,
            inputStyle: 'contenteditable',
            rulers: rulers.value,
        }),
    );

    codemirror.value.on('change', (cm) => {
        emit('update:model-value', cm.doc.getValue());
    });

    codemirror.value.on('focus', () => emit('focus'));
    codemirror.value.on('blur', () => emit('blur'));

    codemirror.value.on('keydown', (cm, e) => {
	    // Handle ESC to blur/unfocus the editor
        if (e.keyCode === 27) {
            e.preventDefault();
            codemirror.value.getInputField().blur();
        }
    });
}

watch(
    () => props.disabled,
    (value) => {
        codemirror.value?.setOption('readOnly', value ? 'nocursor' : false);
    },
    { immediate: true },
);

watch(
    () => props.mode,
    (value) => {
        codemirror.value?.setOption('mode', value);
    },
    { immediate: true },
);

watch(
    () => props.modelValue,
    (value) => {
        if (value === codemirror.value?.doc.getValue()) return;
        if (!value) value = '';

        codemirror.value?.doc.setValue(value);
    },
    { immediate: true },
);

const modeLabel = computed(() => {
    return modes.value.find((m) => m.value === props.mode)?.label || props.mode;
});

const colorMode = computed(() => {
    if (props.colorMode === 'system') {
        return colorModeApi.mode.value === 'dark' ? 'dark' : 'light';
    }

    return props.colorMode;
});

const theme = computed(() => {
    return colorMode.value === 'light' ? 'default' : 'material';
});

const themeClass = computed(() => {
    return `theme-${colorMode.value}`;
});

const rulers = computed(() => {
    if (!props.rulers) {
        return [];
    }

    let rulerColor = colorMode.value === 'light' ? 'var(--theme-color-gray-300)' : 'var(--theme-color-gray-700)';

    return Object.entries(props.rulers).map(([column, style]) => {
        let lineStyle = style === 'dashed' ? 'dashed' : 'solid';

        return {
            column: parseInt(column),
            lineStyle: lineStyle,
            color: rulerColor,
        };
    });
});

watch(theme, (newTheme) => codemirror.value.setOption('theme', newTheme));
watch(rulers, (newRulers) => codemirror.value.setOption('rulers', newRulers));

const showToolbar = computed(() => {
    return props.allowModeSelection || props.showModeLabel;
});

function toggleFullscreen() {
    fullScreenMode.value = !fullScreenMode.value;
}

watch(
    () => fullScreenMode.value,
    (fullScreenMode) => {
        codemirror.value.setOption('fullScreen', fullScreenMode);

        if (!fullScreenMode) {
            codemirrorElement.value.removeAttribute('style');
        }
    },
);
</script>

<template>
    <portal name="code-fullscreen" :disabled="!fullScreenMode" target-class="code-fieldtype">
        <div
            :class="[
                '@container/markdown with-contrast:border with-contrast:border-gray-500 block w-full overflow-hidden rounded-lg bg-white dark:bg-gray-900',
                'text-gray-900 dark:text-gray-300',
                'shadow-ui-sm appearance-none antialiased disabled:shadow-none',
                themeClass,
                { 'code-fullscreen': fullScreenMode },
            ]"
        >
            <publish-field-fullscreen-header
                v-if="fullScreenMode"
                :title="title"
                :field-actions="fieldActions"
                @close="toggleFullscreen"
            >
                <Select
                    class="w-32"
                    size="sm"
                    v-if="allowModeSelection"
                    :options="modes"
                    :disabled="disabled"
                    :model-value="mode"
                    @update:modelValue="$emit('update:mode', $event)"
                />
                <div v-else-if="showModeLabel" v-text="modeLabel" class="font-mono text-xs text-gray-700 dark:text-gray-300"></div>
            </publish-field-fullscreen-header>
            <div
                class="flex items-center justify-between rounded-t-[calc(var(--radius-lg)-1px)] bg-gray-50 px-2 py-1 dark:bg-gray-925 border border-b-0 border-gray-300 dark:border-gray-700 dark:border-b-1 dark:border-b-white/10"
                :class="{ 'border-dashed': readOnly }"
                v-if="showToolbar"
            >
                <div>
                    <Select
                        class="w-auto"
                        size="xs"
                        v-if="allowModeSelection"
                        :options="modes"
                        :disabled="disabled"
                        :model-value="mode"
                        searchable
                        @update:modelValue="$emit('update:mode', $event)"
                    />

                    <span v-else v-text="modeLabel" class="font-mono text-xs text-gray-700 dark:text-gray-300" />
                </div>
            </div>
            <div ref="codemirrorElement" class="font-mono text-sm border border-gray-300 dark:border dark:border-gray-700 dark:bg-gray-900 rounded-lg [&_.CodeMirror]:rounded-lg" :class="{ 'dark:border-t-0 rounded-t-none [&_.CodeMirror]:rounded-t-none': showToolbar }"></div>
        </div>
    </portal>
</template>
