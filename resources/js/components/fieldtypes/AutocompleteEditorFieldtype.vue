<template>
    <AutocompleteEditor
        :model-value="content"
        :options="meta.options"
        :inline="config.inline"
        :enable-line-breaks="config.enable_line_breaks"
        :buttons="config.buttons"
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

const emit = defineEmits(Fieldtype.emits);
const props = defineProps(Fieldtype.props);
const { isReadOnly, update } = Fieldtype.use(emit, props);

// Mention labels aren't stored in the markdown, only the value, so they're
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

const content = computed(() => (props.value ? hydrateMentionLabels(markdownToContent(props.value)) : []));

const updateContent = (content) => update(contentToMarkdown(content));
</script>
