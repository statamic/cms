<script setup lang="ts">
import FieldConditions from './FieldConditions.vue';
import { computed } from 'vue';

const emit = defineEmits(['update:fields']);

const props = defineProps({
    field: { type: Object, required: true },
    fields: { type: Array, required: true },
    suggestableFields: { type: Array, required: true },
    fieldtypes: Array,
});

const conditions = computed(() => ({
    handle: props.field.handle,
    hidden: props.field.hidden,
    if: props.field.if,
    unless: props.field.unless,
    if_any: props.field.if_any,
    unless_any: props.field.unless_any,
    always_save: props.field.always_save,
}));

const suggestableFieldsForField = computed(() =>
    props.suggestableFields.filter((f) => f.pageIndex <= props.field.page_index && f.handle !== props.field.handle),
);

const updateConditions = (updated) => {
    const fields = props.fields.map((field) =>
        field._id === props.field._id
            ? {
                ...field,
                hidden: updated.hidden ?? field.hidden,
                if: updated.if || null,
                unless: updated.unless || null,
                if_any: updated.if_any || null,
                unless_any: updated.unless_any || null,
                always_save: updated.always_save ?? field.always_save,
            }
            : field,
    );

    emit('update:fields', fields);
};
</script>

<template>
    <FieldConditions
        :key="field._id"
        :conditions="conditions"
        :suggestable-fields="suggestableFieldsForField"
        :fieldtypes
        @update:conditions="updateConditions"
    />
</template>
