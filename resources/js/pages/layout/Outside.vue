<script setup>
import StatamicLogo from '@/../svg/statamic-logo-lime.svg';
import useBodyClasses from './body-classes.js';
import useStatamicPageProps from '@/composables/page-props.js';
import { onMounted } from 'vue';
import { colorMode } from '@api';

useBodyClasses('bg-gray-50 dark:bg-gray-900 font-sans leading-normal scheme-light p-2');
const { logos, cmsName } = useStatamicPageProps();
const customLogo = logos?.light?.outside ?? logos?.dark?.outside ?? null;
const lightCustomLogo = logos?.light?.outside ?? null;
const darkCustomLogo = logos?.dark?.outside ?? logos?.light?.outside ?? null;

onMounted(() => {
    let userMode = localStorage.getItem('statamic.color_mode');
    if (userMode === null || userMode === undefined || userMode === 'undefined') userMode = 'auto';
    colorMode.initialize(userMode);
});
</script>

<template>
    <div class="relative mx-auto max-w-[400px] items-center justify-center">
        <div class="flex items-center justify-center py-6">
            <div class="logo relative z-10 max-w-3/4 md:pt-18">
                <template v-if="customLogo">
                    <img
                        :src="lightCustomLogo"
                        :alt="cmsName"
                        class="white-label-logo dark:hidden"
                    />
                    <img
                        :src="darkCustomLogo"
                        :alt="cmsName"
                        class="white-label-logo hidden dark:block"
                    />
                </template>
                <div
                    v-else-if="logos.text"
                    class="mx-auto mb-8 max-w-xs text-center text-lg font-medium opacity-50"
                    v-text="logos.text" />
                <StatamicLogo v-else class="h-6" />
            </div>
        </div>
        <slot />
    </div>
</template>
