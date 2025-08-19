<script setup>
import { Button, PublishContainer, PublishField } from '@statamic/ui';
import PublishFieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import { ref, nextTick } from 'vue';

const emit = defineEmits(['update:values', 'removed', 'enter-pressed']);

const props = defineProps({
    display: { type: String, required: true },
    fields: { type: Array, required: true },
    meta: { type: Object, required: true },
    values: { type: Object, required: true },
});

const fieldContainer = ref(null);

const focusFirstField = async () => {
    await nextTick();
    if (fieldContainer.value) {
        // Look for the first input, textarea, or select element
        const firstInput = fieldContainer.value.querySelector('input:not([readonly]), textarea, select, [contenteditable="true"]');
        if (firstInput) {
            firstInput.focus();
        }
    }
};

const handleKeydown = (event) => {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        emit('enter-pressed');
    }
};

defineExpose({
    focusFirstField
});
</script>

<template>
    <div class="flex items-center justify-between mb-3">
        <PublishContainer
            :model-value="values"
            @update:model-value="$emit('update:values', $event)"
            :meta="meta"
            :track-dirty-state="false"
        >
            <PublishFieldsProvider :fields="fields">
                <div class="flex items-center gap-2 w-full">
                    <div class="w-1/4 user-select-none">
                        <ui-input read-only :value="display" class="focus-within:outline-none" />
                    </div>
                    <div ref="fieldContainer" class="flex-1 flex items-center gap-2" @keydown="handleKeydown">
                        <PublishField
                            v-for="field in fields"
                            :key="field.handle"
                            :config="field"
                            v-slot="{ fieldtypeComponent, fieldtypeComponentProps, fieldtypeComponentEvents }"
                        >
                            <Component
                                :is="fieldtypeComponent"
                                v-bind="fieldtypeComponentProps"
                                v-on="fieldtypeComponentEvents"
                            />
                        </PublishField>
                    </div>
                    <Button @click="$emit('removed')" icon="x" size="sm" variant="ghost" inset />
                </div>
            </PublishFieldsProvider>
        </PublishContainer>
    </div>
</template>
