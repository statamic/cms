<script setup>
import { computed, provide } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { PublishForm } from '@ui';
import { dateFormatter } from '@api';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '@/pages/forms/Layout.vue';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';
import FieldNumberingToggle from '@/components/forms/FieldNumberingToggle.vue';
import { useFieldNumberingPreference } from '@/composables/forms/field-numbering';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

const props = defineProps([
    'form',
    'id',
    'formTitle',
    'status',
    'date',
    'blueprint',
    'values',
    'meta',
]);

const { showFieldNumbers } = useFieldNumberingPreference();

const formattedDate = computed(() => dateFormatter.format(props.date));
const title = computed(() => `${__('Form Submission')} ${props.id}`);

const blueprint = computed(() => {
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
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[title, __(formTitle), __('Forms')]" />

        <PublishForm
            :key="showFieldNumbers ? 'numbered' : null"
            :title="formattedDate"
            :blueprint="blueprint"
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
                <FieldNumberingToggle />
            </template>
        </PublishForm>
    </div>
</template>
