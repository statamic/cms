<script setup lang="ts">
import { computed, watch } from 'vue';
import { VueFinalModal } from 'vue-final-modal'
import uniqid from 'uniqid';

const props = withDefaults(defineProps<{
    name?: string
    adaptive?: boolean
    draggable?: string | false
    clickToClose?: boolean
    focusTrap?: boolean
    height?: number | 'auto'
    width?: number | 'auto'
    scrollable?: boolean
}>(), {
    adaptive: true,
    draggable: false,
    clickToClose: false,
    focusTrap: true,
    scrollable: false,
})

const emit = defineEmits(['opened', 'closed'])

const model = defineModel<boolean>()

watch(model, (newValue) => {
    if (newValue) {
        emit('opened')
    } else {
        emit('closed')
    }
})

const styling = computed(() => {
    const width = props.width ?? 600;
    const height = props.height ?? 'auto';

    return ({
        width: typeof(width) === 'number'
            ? `${width}px`
            : width,
        height: typeof(height) === 'number'
            ? `${height}px`
            : height,
    });
})

// @todo: make transition to be as clean as previously?
// We can apply your own by writing the Vue classes for it and choosing it for content-transition below.

// @todo support "shake" prop.
// @todo support "adaptive" prop.
// @todo support "draggable" prop.

const modalId = computed(() => {
    if (props.name) {
        return props.name
    }

    return uniqid()
})
</script>

<template>
    <VueFinalModal
        :modal-id="modalId"
        class="flex items-start justify-center pt-[5%]"
        overlay-transition="vfm-fade"
        content-transition="vfm-fade"
        v-model="model"
        v-bind="$attrs"
    >
        <div :style="styling">
            <slot />
        </div>
    </VueFinalModal>
</template>