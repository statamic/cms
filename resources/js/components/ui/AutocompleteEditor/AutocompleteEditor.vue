<template>
    <div
        class="autocomplete-editor @container/autocomplete-editor shadow-ui-sm rounded-lg border border-gray-300 bg-white dark:border-gray-700 dark:bg-gray-900"
        :class="{ 'border-dashed': readOnly, 'mode:inline': inline }"
    >
        <div
            v-if="showToolbar && editor"
            class="flex items-center rounded-t-[calc(var(--radius-lg)-1px)] border-b border-gray-300 bg-gray-50 p-1 dark:border-white/10 dark:bg-gray-925"
        >
            <AutocompleteEditorToolbar :editor="editor" :buttons="resolvedButtons" />
        </div>
        <div v-if="initError" class="autocomplete-editor-error p-2 text-sm text-red-500" v-text="initError" />
        <div
            class="autocomplete-editor-content relative focus-within:focus-outline"
            :class="showToolbar ? 'rounded-t-none rounded-b-lg' : 'rounded-lg'"
        >
            <EditorContent :editor="editor" />
        </div>
    </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { EditorContent, useEditor } from '@tiptap/vue-3';
import { Extension } from '@tiptap/core';
import Bold from '@tiptap/extension-bold';
import Italic from '@tiptap/extension-italic';
import Heading from '@tiptap/extension-heading';
import { BulletList, OrderedList, ListItem } from '@tiptap/extension-list';
import HardBreak from '@tiptap/extension-hard-break';
import Paragraph from '@tiptap/extension-paragraph';
import Text from '@tiptap/extension-text';
import History from '@tiptap/extension-history';
import { Placeholder, Dropcursor, Gapcursor } from '@tiptap/extensions';
import { __, clone } from '@/bootstrap/globals';
import { Autocomplete } from './extensions/Autocomplete';
import { DocumentBlock, DocumentInline } from './extensions/Document';
import AutocompleteEditorToolbar from './AutocompleteEditorToolbar.vue';

const props = defineProps({
    modelValue: { type: Array, default: () => [] },
    options: { type: Array, default: () => [] },
    loadOptions: { type: Function, default: null },
    inline: { type: Boolean, default: false },
    enableLineBreaks: { type: Boolean, default: false },
    buttons: { type: Array, default: () => ['bold', 'italic', 'h2', 'h3', 'bulletlist', 'orderedlist'] },
    trigger: { type: String, default: '@' },
    placeholder: { type: String, default: '' },
    readOnly: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue', 'focus', 'blur']);

const initError = ref(null);

const headingLevels = [1, 2, 3, 4, 5, 6];

const buttonDefinitions = {
    bold: {
        name: 'bold',
        text: __('Bold'),
        svg: 'text-bold',
        command: (editor) => editor.chain().focus().toggleBold().run(),
        active: (editor) => editor.isActive('bold'),
    },
    italic: {
        name: 'italic',
        text: __('Italic'),
        svg: 'text-italic',
        command: (editor) => editor.chain().focus().toggleItalic().run(),
        active: (editor) => editor.isActive('italic'),
    },
    bulletlist: {
        name: 'bulletlist',
        text: __('Unordered List'),
        svg: 'list-ul',
        command: (editor) => editor.chain().focus().toggleBulletList().run(),
        active: (editor) => editor.isActive('bulletList'),
    },
    orderedlist: {
        name: 'orderedlist',
        text: __('Ordered List'),
        svg: 'list-ol',
        command: (editor) => editor.chain().focus().toggleOrderedList().run(),
        active: (editor) => editor.isActive('orderedList'),
    },
    ...Object.fromEntries(
        headingLevels.map((level) => [
            `h${level}`,
            {
                name: `h${level}`,
                text: __(`Heading ${level}`),
                svg: `h${level}`,
                command: (editor) => editor.chain().focus().toggleHeading({ level }).run(),
                active: (editor) => editor.isActive('heading', { level }),
            },
        ]),
    ),
};

const resolvedButtons = computed(() => props.buttons.map((name) => buttonDefinitions[name]).filter(Boolean));

const showToolbar = computed(() => !props.inline && !props.readOnly && resolvedButtons.value.length > 0);

function resolveOptions({ query }) {
    if (props.loadOptions) return props.loadOptions({ query });

    const needle = (query ?? '').toLowerCase();

    return props.options.filter((option) => (option.label ?? option.value ?? '').toLowerCase().includes(needle));
}

function keyboardExtension() {
    const disableEnter = props.inline && !props.enableLineBreaks;

    return Extension.create({
        name: 'autocompleteEditorKeymap',
        addKeyboardShortcuts() {
            const shortcuts = {
                'Ctrl-Enter': () => true,
                'Cmd-Enter': () => true,
            };

            if (disableEnter) shortcuts.Enter = () => true;

            return shortcuts;
        },
    });
}

function inlineHardBreak() {
    return HardBreak.extend({
        addKeyboardShortcuts() {
            return {
                ...this.parent?.(),
                Enter: () => this.editor.commands.setHardBreak(),
            };
        },
    });
}

function getExtensions() {
    const enabled = props.buttons;

    const extensions = [
        props.inline ? DocumentInline : DocumentBlock,
        Paragraph,
        Text,
        History,
        Dropcursor,
        Gapcursor,
        Autocomplete.configure({ suggestion: { char: props.trigger, items: resolveOptions } }),
        keyboardExtension(),
    ];

    if (props.inline) {
        if (props.enableLineBreaks) extensions.push(inlineHardBreak());
    } else {
        extensions.push(HardBreak);
    }

    if (props.placeholder) extensions.push(Placeholder.configure({ placeholder: props.placeholder }));

    if (enabled.includes('bold')) extensions.push(Bold);
    if (enabled.includes('italic')) extensions.push(Italic);

    if (!props.inline) {
        if (enabled.includes('bulletlist')) extensions.push(BulletList);
        if (enabled.includes('orderedlist')) extensions.push(OrderedList);
        if (enabled.includes('bulletlist') || enabled.includes('orderedlist')) extensions.push(ListItem);

        const levels = headingLevels.filter((level) => enabled.includes(`h${level}`));
        if (levels.length) extensions.push(Heading.configure({ levels }));
    }

    return extensions;
}

function valueToContent(value) {
    return value && value.length ? { type: 'doc', content: clone(value) } : null;
}

const editor = useEditor({
    extensions: getExtensions(),
    content: valueToContent(props.modelValue),
    editable: !props.readOnly,
    onUpdate: () => emit('update:modelValue', clone(editor.value.getJSON().content)),
    onFocus: () => emit('focus'),
    onBlur: () => emit('blur'),
    onCreate: ({ editor }) => validateContent(editor),
});

function validateContent(instance) {
    const content = valueToContent(props.modelValue);
    if (!content) return;

    try {
        instance.view.state.schema.nodeFromJSON(content);
    } catch (error) {
        const message = invalidError(error);
        if (message) {
            initError.value = message;
        } else {
            initError.value = __('Something went wrong');
            console.error(error);
        }
    }
}

function invalidError(error) {
    const match = error.message.match(
        /^(?:There is no|Unknown) (?:node|mark) type:? (\w*)(?: in this schema)?$/,
    );

    if (match) {
        return match[1]
            ? __('Invalid content, :type button/extension is not enabled', { type: match[1] })
            : __('Invalid content, nodes and marks must have a type');
    }
}

watch(
    () => props.modelValue,
    (value) => {
        const instance = editor.value;
        if (!instance) return;
        if (instance.view.dom.contains(document.activeElement)) return;

        const content = valueToContent(value);
        if (JSON.stringify(content) !== JSON.stringify(instance.getJSON())) {
            instance.commands.setContent(content, { emitUpdate: false });
        }
    },
);

watch(
    () => props.readOnly,
    (readOnly) => editor.value?.setEditable(!readOnly),
);
</script>

<style scoped>
@reference '../../../../css/app.css';

:deep(.ProseMirror) {
    outline: none;
    @apply p-2 text-gray-900 leading-normal @lg/autocomplete-editor:p-4 dark:text-gray-300;
}

.mode\:inline :deep(.ProseMirror) {
    @apply min-h-10 px-3 py-2 leading-[1.375rem];
}

:deep(.ProseMirror :is(p, ol, ul)) {
    @apply st-text-legibility;
    margin-top: 0;
    margin-bottom: 0.85em;
}

.mode\:inline :deep(.ProseMirror p) {
    margin-bottom: 0;
}

:deep(.ProseMirror li > p) {
    margin-bottom: 0;
}

:deep(.ProseMirror :is(strong, b)) {
    @apply font-bold text-gray-800 dark:text-gray-100;
}

:deep(.ProseMirror :is(em, i)) {
    font-style: italic;
}

:deep(.ProseMirror :is(h1, h2, h3, h4, h5, h6)) {
    @apply mt-5 mb-2.5 font-bold text-gray-900 dark:text-gray-100;
}

:deep(.ProseMirror h1) {
    @apply text-3xl;
}

:deep(.ProseMirror h2) {
    @apply text-2xl;
}

:deep(.ProseMirror h3) {
    @apply text-xl;
}

:deep(.ProseMirror h4) {
    @apply text-base;
}

:deep(.ProseMirror h5) {
    @apply text-sm;
}

:deep(.ProseMirror h6) {
    @apply text-2xs uppercase tracking-wider text-gray-800 dark:text-gray-500;
}

:deep(.ProseMirror ul) {
    list-style-type: disc;
}

:deep(.ProseMirror ol) {
    list-style-type: decimal;
}

:deep(.ProseMirror :is(ol, ul)) {
    @apply ms-4 ps-4;
}

:deep(.ProseMirror li :is(ol, ul)) {
    @apply my-0 ms-4 ps-4;
}

:deep(.ProseMirror li ul) {
    list-style-type: circle;
}

:deep(.ProseMirror li ol) {
    list-style-type: decimal;
}

:deep(.ProseMirror > :first-child) {
    margin-top: 0;
}
</style>
