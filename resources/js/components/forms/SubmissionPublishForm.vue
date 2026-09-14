<script setup>
import { computed, provide } from 'vue';
import { PublishForm } from '@ui';
import { dateFormatter } from '@api';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';

const props = defineProps({
    status: { type: String, required: true },
    date: { type: String, required: true },
    blueprint: { type: Object, required: true },
    values: { type: Object, required: true },
    meta: { type: Object, required: true },
});

const { showFieldNumbers } = useFieldNumberingPreference();

const formattedDate = computed(() => dateFormatter.format(props.date));

const numberedBlueprint = computed(() => {
    if (!showFieldNumbers.value) return props.blueprint;

    let number = 0;

    return {
        ...props.blueprint,
        tabs: (props.blueprint.tabs || []).map((tab) => ({
            ...tab,
            sections: (tab.sections || []).map((section) => ({
                ...section,
                fields: (section.fields || []).map((field) => {
                    if (['form_heading', 'form_paragraph', 'form_banner'].includes(field.type)) return field;

                    return {
                        ...field,
                        display: `${++number}. ${field.display ?? field.handle}`,
                    };
                }),
            })),
        })),
    };
});

provide('isFormSubmission', true);
</script>

<template>
    <PublishForm
        :key="showFieldNumbers ? 'numbered' : null"
        :blueprint="numberedBlueprint"
        :initial-values="values"
        :initial-meta="meta"
        :submit-url="null"
        read-only
    >
        <template #title>
            <SubmissionStatusIndicator :status="status" />
            {{ formattedDate }}
        </template>
        <template #actions>
            <slot name="actions" />
            <FieldNumberingToggle />
        </template>
    </PublishForm>
</template>
