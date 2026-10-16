<script setup>
import { ref, watch } from 'vue';
import { Stack } from '@ui';
import InlineSubmissionForm from '@/components/forms/InlineSubmissionForm.vue';
import SubmissionListing from '@/components/forms/SubmissionListing.vue';

defineProps({
    form: { type: String, required: true },
    actionUrl: { type: String, required: true },
    filters: { required: true },
});

const open = defineModel('open', { type: Boolean, default: false });

const viewingSubmission = ref(null);

watch(open, (open) => {
    if (!open) viewingSubmission.value = null;
});
</script>

<template>
    <Stack v-model:open="open" :title="__('Submissions')">
        <SubmissionListing
            :form="form"
            :action-url="actionUrl"
            :filters="filters"
            view-in-stack
            sort-column="datestamp"
            sort-direction="desc"
            @view="viewingSubmission = $event"
        />

        <InlineSubmissionForm
            v-if="viewingSubmission"
            :url="viewingSubmission.url"
            @closed="viewingSubmission = null"
        />
    </Stack>
</template>
