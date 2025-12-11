<script setup>
import { Link } from '@inertiajs/vue3';
import Head from '@/pages/layout/Head.vue';
import { Header, CardPanel, Heading, Subheading, DocsCallout } from '@ui';

defineProps(['utilities']);
</script>

<template>
    <Head :title="__('Utilities')" />

    <div class="max-w-5xl mx-auto">
        <Header :title="__('Utilities')" icon="utilities" />

        <CardPanel>
            <div class="flex flex-wrap starting-style-transition-children">
                <Link
                    v-for="utility in utilities"
                    :key="utility.url"
                    :href="utility.url"
                    class="group w-full items-start rounded-md border border-transparent px-2 py-3 lg:p-4 hover:bg-gray-100 dark:hover:border-dark-400 dark:hover:bg-dark-575 md:flex lg:w-1/2"
                >
                    <div class="size-6 text-gray-400 mt-1 mb-2 me-4" v-html="utility.icon" />
                    <div class="flex-1 md:me-6">
                        <Heading size="lg" :text="utility.title" />
                        <Subheading v-text="utility.description" />
                    </div>
                </Link>
            </div>
        </CardPanel>

        <DocsCallout :topic="__('Utilities')" url="extending/utilities" />
    </div>
</template>
