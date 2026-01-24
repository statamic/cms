<script setup>
import { computed, provide } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { PublishForm } from '@ui';
import { dateFormatter } from '@api';

const props = defineProps([
    'id',
    'formTitle',
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
            icon="forms"
            :title="formattedDate"
            :blueprint="blueprint"
            :initial-values="values"
            :initial-meta="meta"
            :submit-url="null"
            read-only
        />
    </div>
</template>
