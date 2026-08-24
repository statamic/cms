<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { Icon, Stack } from '@ui';
import SubmissionPublishForm from '@/components/forms/SubmissionPublishForm.vue';

const props = defineProps({
    url: { type: String, required: true },
});

const emit = defineEmits(['closed']);

const loading = ref(true);
const submission = ref(null);

axios
    .get(props.url, { headers: { Accept: 'application/json' } })
    .then((response) => {
        submission.value = response.data;
        loading.value = false;
    })
    .catch((error) => {
        Statamic.$toast.error(error.response?.data?.message || __('Something went wrong'));
        emit('closed');
    });
</script>

<template>
    <Stack open @closed="emit('closed')">
        <div v-if="loading" class="absolute inset-0 z-200 flex items-center justify-center text-center">
            <Icon name="loading" />
        </div>

        <SubmissionPublishForm
            v-else
            :status="submission.status"
            :date="submission.date"
            :blueprint="submission.blueprint"
            :values="submission.values"
            :meta="submission.meta"
        />
    </Stack>
</template>
