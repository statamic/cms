<script setup>
import { computed, provide } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { PublishForm } from '@ui';
import { dateFormatter } from '@api';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '@/pages/forms/Layout.vue';
import SubmissionStatusIndicator from '@/components/forms/SubmissionStatusIndicator.vue';

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

const formattedDate = computed(() => dateFormatter.format(props.date));
const title = computed(() => `${__('Form Submission')} ${props.id}`);

provide('isFormSubmission', true);
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[title, formTitle, __('Forms')]" />

        <PublishForm
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
        </PublishForm>
    </div>
</template>
