<script setup>
import { EditableArea, EditableInput, EditablePreview, EditableRoot } from 'reka-ui';
import { useTemplateRef } from 'vue';

const emit = defineEmits(['update:modelValue', 'cancel', 'submit', 'edit']);

const props = defineProps({
    modelValue: { type: String, default: null },
    startWithEditMode: { type: Boolean, default: false },
    submitMode: { type: String, default: 'blur' },
    placeholder: { type: String, default: 'Enter text...' },
});

const editableRoot = useTemplateRef('root');

defineExpose({
    edit,
});

function stateUpdated(state) {
    console.log('state updated', state)

    if (state === 'cancel') {
        emit('update:modelValue', null);
        emit('cancel');
    }

    if (state === 'submit') {
        emit('submit', props.modelValue);
    }

    if (state === 'edit') {
        emit('edit', props.modelValue);
    }
};

function edit() {
    editableRoot.value.edit();
}
</script>

<template>
    <EditableRoot
        ref="root"
        :placeholder
        :startWithEditMode
        :submitMode
        :modelValue="modelValue"
        @update:modelValue="emit('update:modelValue', $event)"
        @update:state="stateUpdated"
    >
        <EditableArea>
            <EditablePreview />
            <EditableInput />
        </EditableArea>
    </EditableRoot>
</template>
