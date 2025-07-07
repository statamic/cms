<script setup>
import CodeMirror from 'codemirror';
import { computed, markRaw, nextTick, onMounted, ref, useAttrs, useTemplateRef, watch } from 'vue';
import ElementContainer from '@statamic/components/ElementContainer.vue';
import { Select } from '@statamic/ui';

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
    theme: { type: String, default: 'material' },
    rulers: { type: Object, default: () => {} },
    disabled: { type: Boolean, default: false },
    keyMap: { type: String, default: 'sublime' },
    tabSize: { type: Number, required: false },
    indentType: { type: String, default: 'tabs' },
    lineNumbers: { type: Boolean, default: true },
    lineWrapping: { type: Boolean, default: true },
    allowModeSelection: { type: Boolean, default: true },
    showModeLabel: { type: Boolean, default: true },
    mode: { type: String, default: 'javascript' },
    modelValue: { type: String, default: '' },
    title: { type: String, default: () => __('Code Editor') },
    fieldActions: { type: Array, default: () => [] },
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
    refresh,
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
            readOnly: props.disabled ? 'nocursor' : false,
            theme: exactTheme.value,
            inputStyle: 'contenteditable',
            rulers: rulers,
        }),
    );

    codemirror.value.on('change', (cm) => {
        emit('update:model-value', cm.doc.getValue());
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

const exactTheme = computed(() => {
    return props.theme === 'light' ? 'default' : 'material';
});

const themeClass = computed(() => {
    return `theme-${props.theme}`;
});

const rulers = computed(() => {
    if (!props.rulers) {
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
                '@container/markdown block w-full overflow-hidden rounded-lg bg-white dark:bg-gray-900',
                // 'border border-gray-300 dark:border-x-0 dark:border-t-0 dark:border-white/15 dark:inset-shadow-2xs dark:inset-shadow-black',
                'text-gray-800 dark:text-gray-300',
                'shadow-ui-sm not-prose appearance-none antialiased disabled:shadow-none',
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
                <div v-else-if="showModeLabel" v-text="modeLabel" class="font-mono text-xs text-gray-700"></div>
            </publish-field-fullscreen-header>
            <div
                class="flex items-center justify-between rounded-t-xl bg-gray-50 px-2 py-1 dark:bg-gray-950 border border-b-0 border-gray-300 dark:border-none"
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

                    <span v-else v-text="modeLabel" class="font-mono text-xs text-gray-700" />
                </div>
            </div>
            <ElementContainer @resized="refresh">
                <div ref="codemirrorElement" class="font-mono text-sm"></div>
            </ElementContainer>
        </div>
    </portal>
</template>
