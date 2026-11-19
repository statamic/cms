<script setup>
import Layout from '@/pages/layout/Layout.vue';
import PanelLayout from '@/pages/layout/PanelLayout.vue';
import FormsLayout from './Layout.vue';
import { Card, Description, Header, Heading, Icon } from '@ui';
import FormStatusIndicator from '@/components/forms/FormStatusIndicator.vue';
import Head from '@/pages/layout/Head.vue';

defineOptions({ layout: [Layout, PanelLayout, FormsLayout] });

defineProps({
    form: Object,
});
</script>

<template>
    <Head :title="[__('Forms Pro required'), __(form.title), __('Forms')]" />

    <div class="mx-auto max-w-5xl">
        <Header class="mb-2">
            <template #title>
                <FormStatusIndicator :status="form.status" />
                {{ __(form.title) }}
            </template>
        </Header>

        <Card class="flex flex-col items-center gap-2 py-12 text-center">
            <Icon name="padlock-locked" class="size-6 text-gray-500 dark:text-gray-400" />
            <Heading size="lg" :text="__('Forms Pro required')" />
            <Description class="max-w-md" :text="__('statamic::messages.form_forms_pro_required')" />
        </Card>
    </div>
</template>
