<script setup>
import { Button, Input } from '@statamic/ui';
import { injectContainerContext } from './Container.vue';
import { computed } from 'vue';

const context = injectContainerContext();
const store = context.store;

const props = defineProps({
    handle: {
        type: String,
        required: true,
    },
    display: {
        type: String,
        default: '',
    },
    instructions: {
        type: String,
        default: '',
    },
});

const value = computed(() => {
    return store.values[props.handle];
});

function updateValueInStore(value) {
    store.setFieldValue({ handle: props.handle, value });
}
</script>

<template>
    <div class="mb-4">
        <Input
            :label="display"
            :description="instructions"
            :model-value="value"
            @update:model-value="updateValueInStore"
        />

        <!--        <Button @click="context.someFunction" text="Click me" />-->
    </div>
</template>
