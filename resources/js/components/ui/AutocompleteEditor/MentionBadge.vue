<script setup>
import { NodeViewWrapper, nodeViewProps } from '@tiptap/vue-3';
import Badge from '../Badge.vue';

const props = defineProps(nodeViewProps);
</script>

<template>
    <NodeViewWrapper as="span" class="inline-flex align-middle leading-none" contenteditable="false">
        <Badge size="sm" :text="props.node.attrs.label ?? props.node.attrs.value" />
    </NodeViewWrapper>
</template>
