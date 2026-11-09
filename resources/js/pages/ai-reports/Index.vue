<script setup>
import Head from '@/pages/layout/Head.vue';
import { Header, Button, Listing } from '@ui';
import { Link } from '@inertiajs/vue3';

// Prototype (phase 2): Tools → AI Reports, one row for each scheduled prompt on Settings → AI, like Forms (see AiReportsController)
defineProps(['prompts', 'columns', 'settingsUrl']);
</script>

<template>
    <Head :title="__('AI Reports')" />

    <div class="max-w-page mx-auto">
        <Header :title="__('AI Reports')" icon="pulse">
            <Button :href="settingsUrl" :text="__('Scheduled Prompts')" icon="cog" />
        </Header>

        <Listing :items="prompts" :columns :allow-presets="false" :allow-customizing-columns="false" :allow-search="false">
            <template #cell-title="{ row: prompt }">
                <Link :href="prompt.show_url">{{ prompt.title }}</Link>
            </template>
        </Listing>
    </div>
</template>
