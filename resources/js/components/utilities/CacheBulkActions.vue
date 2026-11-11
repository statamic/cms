<script setup>
import { computed, onBeforeUnmount, ref, watch } from 'vue';
import { injectListingContext } from '@ui/Listing/Listing.vue';
import BulkActionsFloatingToolbar from '@ui/Listing/BulkActionsFloatingToolbar.vue';

const props = defineProps({
    label: {
        type: String,
        required: true,
    },
    processing: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['run']);
const { selections, clearSelections } = injectListingContext();
const visible = ref(false);
let timeout = null;

const actions = computed(() => [
    {
        handle: 'cache',
        title: props.label,
        run() {
            if (!props.processing) {
                emit('run', [...selections.value]);
            }
        },
    },
]);

function deselect() {
    if (!props.processing) {
        clearSelections();
    }
}

watch(() => selections.value.length > 0, (selected) => {
    clearTimeout(timeout);

    if (selected) {
        timeout = setTimeout(() => visible.value = true, 300);
    } else {
        visible.value = false;
    }
}, { immediate: true });

onBeforeUnmount(() => {
    clearTimeout(timeout);
});
</script>

<template>
    <BulkActionsFloatingToolbar
        :actions="actions"
        :visible="visible"
        :selections="selections"
        :clear-selections="deselect"
        :inert="processing || undefined"
        :aria-busy="processing || undefined"
    />
</template>
