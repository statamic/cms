<script setup>
import { computed } from 'vue';
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import { __ } from '@/bootstrap/globals';
import Badge from '../Badge.vue';

const props = defineProps(nodeViewProps);

const missing = computed(() => props.extension.options.isMissing(props.node.attrs.value));

const text = computed(() => props.node.attrs.label ?? props.node.attrs.value);
</script>

<template>
    <NodeViewWrapper as="span" class="inline-flex align-middle leading-none" contenteditable="false">
        <Badge
            size="sm"
            :text="text"
            :color="missing ? 'red' : 'default'"
            :class="{ 'line-through': missing }"
            :title="missing ? __('This item no longer exists') : null"
        />
    </NodeViewWrapper>
</template>
