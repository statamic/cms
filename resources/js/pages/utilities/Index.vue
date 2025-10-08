<script setup>
import { Link } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, CardPanel, Heading, Subheading, DocsCallout } from '@ui';

defineProps(['utilities']);
</script>

<template>
    <Head :title="__('Utilities')" />

    <Header :title="__('Utilities')" icon="utilities" />

    <CardPanel>
        <div class="flex flex-wrap starting-style-transition-children">
            <Link
                v-for="utility in utilities"
                :key="utility.url"
                :href="utility.url"
                class="group w-full items-start rounded-md border border-transparent p-4 hover:bg-gray-100 dark:hover:border-dark-400 dark:hover:bg-dark-575 md:flex lg:w-1/2"
            >
                <div class="size-6 text-gray-400 mt-1 me-4" v-html="utility.icon" />
                <div class="mb-4 flex-1 md:mb-0 md:me-6">
                    <Heading size="lg" :text="utility.title" />
                    <Subheading v-text="utility.description" />
                </div>
            </Link>
        </div>
    </CardPanel>

    <DocsCallout :topic="__('Utilities')" url="extending/utilities" />
</template>
