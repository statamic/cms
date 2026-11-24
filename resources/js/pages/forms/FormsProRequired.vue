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
            <div class="relative mb-4 h-16 w-14" aria-hidden="true">
                <div class="absolute inset-0 translate-x-3 -translate-y-2 rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-850"></div>
                <div class="absolute inset-0 translate-x-1.5 -translate-y-1 rounded-md border border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-850"></div>
                <div class="absolute inset-0 flex flex-col gap-1.25 rounded-md border border-gray-300 bg-white p-2 shadow-sm dark:border-gray-600 dark:bg-gray-800">
                    <div class="h-0.75 w-2/3 rounded-full bg-gray-300 dark:bg-gray-600"></div>
                    <div class="h-2 rounded-sm border border-gray-200 dark:border-gray-600"></div>
                    <div class="h-2 rounded-sm border border-gray-200 dark:border-gray-600"></div>
                </div>
                <!-- Its shapes are filled with the page colour, so the page behind doesn't show through -->
                <Icon name="padlock-locked" class="absolute -right-2 -bottom-2 size-5 text-gray-500 dark:text-gray-400 [&_path]:fill-white dark:[&_path]:fill-gray-800" />
            </div>
            <Heading size="lg" :text="__('Forms Pro required')" />
            <Description class="max-w-sm" :text="__('statamic::messages.form_forms_pro_required')" />
        </Card>
    </div>
</template>
