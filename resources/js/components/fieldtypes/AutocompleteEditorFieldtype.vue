<template>
    <AutocompleteEditor
        :model-value="content"
        :options="meta.options"
        :inline="markdown ? config.inline : true"
        :enable-line-breaks="config.enable_line_breaks"
        :buttons="markdown ? config.buttons : []"
        :trigger="config.trigger"
        :placeholder="config.placeholder"
        :read-only="isReadOnly"
        @update:model-value="updateContent"
    />
</template>

<script setup>
import { computed } from 'vue';
import Fieldtype from '@/components/fieldtypes/fieldtype.js';
import { AutocompleteEditor } from '@/components/ui';
import { contentToMarkdown, markdownToContent } from '@/components/ui/AutocompleteEditor/markdown';
import { contentToText, textToContent } from '@/components/ui/AutocompleteEditor/text';

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

// Without markdown the editor is limited to a single paragraph of text and mentions,
// so there's no formatting for the schema to allow and nothing for a toolbar to do.
const markdown = props.config.markdown !== false;
const parse = markdown ? markdownToContent : textToContent;
const serialize = markdown ? contentToMarkdown : contentToText;

// Mention labels aren't stored, only the value, so they're
// resolved from the configured options every time content is loaded.
function hydrateMentionLabels(nodes) {
    const labels = Object.fromEntries(props.meta.options.map((option) => [option.value, option.label]));

    return nodes.map((node) => {
        if (node.type === 'mention') {
            const label = labels[node.attrs.value];

            return label ? { ...node, attrs: { ...node.attrs, label } } : node;
        }

        if (node.content) {
            return { ...node, content: hydrateMentionLabels(node.content) };
        }

        return node;
    });
}

const content = computed(() => (props.value ? hydrateMentionLabels(parse(props.value)) : []));

const updateContent = (content) => update(serialize(content));
</script>
