<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Listing } from '@ui';
import { Link } from '@inertiajs/vue3';

// Prototype (phase 2): one scheduled prompt's reports, newest first, like a form's submissions (see AiReportsController)
defineProps(['title', 'schedule', 'runs', 'columns']);
</script>

<template>
    <div class="max-w-5xl 3xl:max-w-6xl mx-auto" data-max-width-wrapper>
        <Head :title="[title, __('AI Reports')]" />

        <Header :title="title" icon="pulse">
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ schedule }}</span>
        </Header>

        <Listing :items="runs" :columns :sortable="false" :allow-presets="false" :allow-customizing-columns="false" :allow-search="false">
            <template #cell-date="{ row: run }">
                <Link :href="run.url">{{ run.date }}</Link>
            </template>
        </Listing>
    </div>
</template>
