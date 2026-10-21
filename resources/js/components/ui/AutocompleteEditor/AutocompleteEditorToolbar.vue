<template>
    <div class="no-select flex flex-1 flex-wrap items-center gap-1">
        <EditorToolbarButton
            v-for="button in buttons"
            :key="button.name"
            :button="button"
            :editor="editor"
            :active="isActive(button)"
        />
    </div>
</template>

<script setup>
import { onBeforeUnmount, ref } from 'vue';
import EditorToolbarButton from './EditorToolbarButton.vue';

const props = defineProps({
    editor: { type: Object, required: true },
    buttons: { type: Array, default: () => [] },
});

const tick = ref(0);
const bump = () => tick.value++;

props.editor.on('transaction', bump);
onBeforeUnmount(() => props.editor.off('transaction', bump));

function isActive(button) {
    tick.value;
    return button.active ? button.active(props.editor) : false;
}
</script>
