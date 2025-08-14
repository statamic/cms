<script setup>
import { PublishContainer, PublishFields } from '@statamic/ui';
import PublishFieldsProvider from '@statamic/components/ui/Publish/FieldsProvider.vue';
import { computed, nextTick, ref, watch } from 'vue';

const emit = defineEmits(['changed']);

const props = defineProps({
    filter: Object,
    values: Object,
});

const defaultValues = computed(() => {
    return props.filter.values || {};
});

const containerValues = ref({ ...defaultValues.value, ...props.values });

const resettingToDefault = ref(false);

watch(() => props.values, (newValues) => {
    if (! newValues) {
        resettingToDefault.value = true;
        containerValues.value = { ...defaultValues.value };
        nextTick(() => resettingToDefault.value = false);
    }
});

const fields = computed(() => {
    if (props.filter.fields.length === 1) {
        props.filter.fields[0].hide_display = true;
    }

    return props.filter.fields;
});

function updateValues(values) {
    let filteredValues = {...values};

    Object.keys(values).forEach((key) => {
        if (values[key] === null || values[key] === undefined) delete filteredValues[key];
    });

    if (resettingToDefault.value) return;

    emit('changed', filteredValues);
}
</script>

<template>
    <PublishContainer
        :model-value="containerValues"
        @update:model-value="updateValues"
        :meta="filter.meta"
        :track-dirty-state="false"
    >
        <PublishFieldsProvider :fields="fields">
            <PublishFields />
        </PublishFieldsProvider>
    </PublishContainer>
</template>
