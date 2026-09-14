<script setup>
import { computed } from 'vue';
import Head from '@/pages/layout/Head.vue';
import { Badge, StatusIndicator } from '@ui';
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from '@/pages/forms/Layout.vue';
import SubmissionPublishForm from '@/components/forms/SubmissionPublishForm.vue';

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
    'entry',
]);

const title = computed(() => `${__('Form Submission')} ${props.id}`);
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[title, __(formTitle), __('Forms')]" />

        <SubmissionPublishForm :status :date :blueprint :values :meta>
            <template #actions>
                <Badge v-if="entry" size="lg" :href="entry.edit_url" target="_blank">
                    <StatusIndicator v-if="entry.status" :status="entry.status" class="h-1" />
                    <span v-text="entry.title || __('Deleted entry')" />
                </Badge>
            </template>
        </SubmissionPublishForm>
    </div>
</template>
