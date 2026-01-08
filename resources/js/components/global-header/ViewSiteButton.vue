<script setup>
import { Icon } from '@ui';
import useStatamicPageProps from '@/composables/page-props.js';

const { selectedSiteUrl: url } = useStatamicPageProps();
</script>

<template>
    <a
        :href="url"
        :aria-label="__('View Site')"
        class="inline-flex items-center justify-center whitespace-nowrap shrink-0 font-medium antialiased cursor-pointer no-underline disabled:text-white/60 dark:disabled:text-white/50 disabled:cursor-not-allowed [&_svg]:shrink-0 [&_svg]:text-gray-925 [&_svg]:opacity-60 dark:[&_svg]:text-white bg-transparent hover:bg-gray-400/10 text-gray-900 dark:text-gray-300 dark:hover:bg-white/15 dark:hover:text-gray-200 h-8 text-[0.8125rem] leading-tight rounded-lg px-0 gap-0 w-8 [&_svg]:size-4 -me-2 [&_svg]:text-white/85! v-popper--has-tooltip"
        target="_blank"
    >
        <Icon name="visit-website" />
    </a>
</template>
