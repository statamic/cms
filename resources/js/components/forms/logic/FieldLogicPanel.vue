<script setup lang="ts">
import FieldConditions from './FieldConditions.vue';
import { computed } from 'vue';

const emit = defineEmits(['update:conditions']);

const props = defineProps({
    field: { type: Object, required: true },
    pageIndex: { type: Number, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const conditions = computed(() => ({
    handle: props.field.handle,
    hidden: props.field.config?.hidden,
    if: props.field.config?.if,
    unless: props.field.config?.unless,
    if_any: props.field.config?.if_any,
    unless_any: props.field.config?.unless_any,
    always_save: props.field.config?.always_save,
}));

const suggestableFieldsForField = computed(() =>
    props.suggestableFields.filter((f) => f.pageIndex <= props.pageIndex && f.handle !== props.field.handle),
);
</script>

<template>
    <div class="@container w-full">
        <FieldConditions
            :key="field._id"
            :conditions="conditions"
            :suggestable-fields="suggestableFieldsForField"
            :fieldtypes
            @update:conditions="emit('update:conditions', $event)"
        />
    </div>
</template>
